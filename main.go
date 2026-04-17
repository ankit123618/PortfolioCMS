package main

import (
	"context"
	"crypto/hmac"
	"crypto/rand"
	"crypto/sha256"
	"database/sql"
	"encoding/base64"
	"encoding/hex"
	"encoding/json"
	"errors"
	"fmt"
	"html/template"
	"io"
	"log"
	"net/http"
	"os"
	"path/filepath"
	"regexp"
	"sort"
	"strconv"
	"strings"
	"time"

	_ "github.com/go-sql-driver/mysql"
	"golang.org/x/crypto/bcrypt"
)

const (
	adminCookieName = "portfolio_admin"
	maxUploadMemory = 32 << 20
)

var (
	projectFieldPattern = regexp.MustCompile(`^projects\[(\d+)\]\[(title|description)\]$`)
	contactFieldPattern = regexp.MustCompile(`^contact\[(\d+)\]\[(name|value)\]$`)
	skillsFieldPattern  = regexp.MustCompile(`^skills\[(\d+)\]$`)
)

type config struct {
	DBHost    string
	DBPort    string
	DBName    string
	DBUser    string
	DBPass    string
	DBCharset string
	Addr      string
	AppSecret string
}

type app struct {
	db        *sql.DB
	templates *template.Template
	cfg       config
}

type pageSchema struct {
	Page     string    `json:"page"`
	Sections []section `json:"sections"`
}

type section struct {
	Type    string                 `json:"type"`
	ID      string                 `json:"id"`
	Enabled bool                   `json:"enabled"`
	Data    map[string]interface{} `json:"data"`
}

type skillRow struct {
	Index int
	Value string
}

type projectRow struct {
	Index       int
	Title       string
	Description string
	Image       string
}

type contactRow struct {
	Index int
	Name  string
	Value string
}

type editorViewData struct {
	Error       string
	HeroTitle   string
	HeroTagline string
	HeroPhoto   string
	HeroEnabled bool
	AboutText   string
	VisionText  string
	Skills      []skillRow
	Projects    []projectRow
	Contacts    []contactRow
}

type loginViewData struct {
	Error string
}

func main() {
	cfg := loadConfig()

	dsn := fmt.Sprintf("%s:%s@tcp(%s:%s)/%s?parseTime=true&charset=%s",
		cfg.DBUser,
		cfg.DBPass,
		cfg.DBHost,
		cfg.DBPort,
		cfg.DBName,
		cfg.DBCharset,
	)

	db, err := sql.Open("mysql", dsn)
	if err != nil {
		log.Fatalf("open db: %v", err)
	}
	defer db.Close()

	ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
	defer cancel()
	if err := db.PingContext(ctx); err != nil {
		log.Fatalf("ping db: %v", err)
	}

	tmpl := template.Must(template.ParseFiles(
		"templates/public_index.html",
		"templates/admin_login.html",
		"templates/admin_editor.html",
	))

	a := &app{
		db:        db,
		templates: tmpl,
		cfg:       cfg,
	}

	mux := http.NewServeMux()
	mux.HandleFunc("/", a.handleHome)
	mux.HandleFunc("/api/pages", a.handleGetPage)
	mux.HandleFunc("/admin/login", a.handleLogin)
	mux.HandleFunc("/admin/logout", a.handleLogout)
	mux.HandleFunc("/admin/editor", a.handleEditor)
	mux.Handle("/public/css/", http.StripPrefix("/public/css/", http.FileServer(http.Dir("public/css"))))
	mux.Handle("/public/js/", http.StripPrefix("/public/js/", http.FileServer(http.Dir("public/js"))))
	mux.Handle("/uploads/", http.StripPrefix("/uploads/", http.FileServer(http.Dir("uploads"))))
	mux.HandleFunc("/healthz", func(w http.ResponseWriter, r *http.Request) {
		w.WriteHeader(http.StatusOK)
		_, _ = w.Write([]byte("ok"))
	})

	server := &http.Server{
		Addr:         cfg.Addr,
		Handler:      loggingMiddleware(mux),
		ReadTimeout:  15 * time.Second,
		WriteTimeout: 30 * time.Second,
		IdleTimeout:  60 * time.Second,
	}

	log.Printf("listening on %s", cfg.Addr)
	log.Fatal(server.ListenAndServe())
}

func loadConfig() config {
	env := parseEnvFile(".env")

	cfg := config{
		DBHost:    firstNonEmpty(os.Getenv("DB_HOST"), env["DB_HOST"], "127.0.0.1"),
		DBPort:    firstNonEmpty(os.Getenv("DB_PORT"), env["DB_PORT"], "3306"),
		DBName:    firstNonEmpty(os.Getenv("DB_NAME"), env["DB_NAME"], "portfolio"),
		DBUser:    firstNonEmpty(os.Getenv("DB_USER"), env["DB_USER"], "ankit"),
		DBPass:    firstNonEmpty(os.Getenv("DB_PASS"), env["DB_PASS"]),
		DBCharset: firstNonEmpty(os.Getenv("DB_CHARSET"), env["DB_CHARSET"], "utf8mb4"),
		Addr:      firstNonEmpty(os.Getenv("APP_ADDR"), env["APP_ADDR"], ":8080"),
		AppSecret: firstNonEmpty(os.Getenv("APP_SECRET"), env["APP_SECRET"]),
	}

	if cfg.AppSecret == "" {
		cfg.AppSecret = randomSecret()
		log.Printf("APP_SECRET not set; generated ephemeral secret for this process")
	}

	return cfg
}

func parseEnvFile(path string) map[string]string {
	values := map[string]string{}
	data, err := os.ReadFile(path)
	if err != nil {
		return values
	}

	for _, line := range strings.Split(string(data), "\n") {
		line = strings.TrimSpace(line)
		if line == "" || strings.HasPrefix(line, "#") {
			continue
		}
		parts := strings.SplitN(line, "=", 2)
		if len(parts) != 2 {
			continue
		}
		key := strings.TrimSpace(parts[0])
		val := strings.TrimSpace(parts[1])
		val = strings.Trim(val, `"'`)
		values[key] = val
	}

	return values
}

func randomSecret() string {
	buf := make([]byte, 32)
	if _, err := rand.Read(buf); err != nil {
		return "dev-secret-change-me"
	}
	return hex.EncodeToString(buf)
}

func firstNonEmpty(values ...string) string {
	for _, v := range values {
		if strings.TrimSpace(v) != "" {
			return v
		}
	}
	return ""
}

func loggingMiddleware(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		start := time.Now()
		next.ServeHTTP(w, r)
		log.Printf("%s %s %s", r.Method, r.URL.Path, time.Since(start).Truncate(time.Millisecond))
	})
}

func (a *app) handleHome(w http.ResponseWriter, r *http.Request) {
	if r.URL.Path != "/" {
		http.NotFound(w, r)
		return
	}
	if err := a.templates.ExecuteTemplate(w, "public_index.html", nil); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
	}
}

func (a *app) handleGetPage(w http.ResponseWriter, r *http.Request) {
	slug := r.URL.Query().Get("slug")
	if slug == "" {
		slug = "home"
	}

	var schema string
	err := a.db.QueryRowContext(r.Context(), "SELECT `schema` FROM pages WHERE slug = ? LIMIT 1", slug).Scan(&schema)
	if errors.Is(err, sql.ErrNoRows) {
		w.Header().Set("Content-Type", "application/json; charset=utf-8")
		_, _ = w.Write([]byte("{}"))
		return
	}
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json; charset=utf-8")
	_, _ = w.Write([]byte(schema))
}

func (a *app) handleLogin(w http.ResponseWriter, r *http.Request) {
	switch r.Method {
	case http.MethodGet:
		a.renderLogin(w, loginViewData{})
	case http.MethodPost:
		if err := r.ParseForm(); err != nil {
			a.renderLogin(w, loginViewData{Error: "Could not read the login form."})
			return
		}

		username := strings.TrimSpace(r.FormValue("username"))
		password := r.FormValue("password")

		var hashed string
		err := a.db.QueryRowContext(r.Context(), "SELECT password FROM admin_users WHERE username = ? LIMIT 1", username).Scan(&hashed)
		if err != nil {
			a.renderLogin(w, loginViewData{Error: "Invalid username or password."})
			return
		}

		if bcrypt.CompareHashAndPassword([]byte(hashed), []byte(password)) != nil {
			a.renderLogin(w, loginViewData{Error: "Invalid username or password."})
			return
		}

		a.setAdminCookie(w)
		http.Redirect(w, r, "/admin/editor", http.StatusSeeOther)
	default:
		http.Error(w, "method not allowed", http.StatusMethodNotAllowed)
	}
}

func (a *app) renderLogin(w http.ResponseWriter, data loginViewData) {
	if err := a.templates.ExecuteTemplate(w, "admin_login.html", data); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
	}
}

func (a *app) handleLogout(w http.ResponseWriter, r *http.Request) {
	http.SetCookie(w, &http.Cookie{
		Name:     adminCookieName,
		Value:    "",
		Path:     "/",
		MaxAge:   -1,
		HttpOnly: true,
		SameSite: http.SameSiteLaxMode,
	})
	http.Redirect(w, r, "/admin/login", http.StatusSeeOther)
}

func (a *app) handleEditor(w http.ResponseWriter, r *http.Request) {
	if !a.isAdmin(r) {
		http.Redirect(w, r, "/admin/login", http.StatusSeeOther)
		return
	}

	pageID, schema, err := a.fetchPageSchema(r.Context(), "home")
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	if r.Method == http.MethodPost {
		if err := r.ParseMultipartForm(maxUploadMemory); err != nil {
			a.renderEditor(w, buildEditorView(schema, "Could not read the submitted form."))
			return
		}

		if err := a.applyEditorChanges(r, &schema); err != nil {
			a.renderEditor(w, buildEditorView(schema, err.Error()))
			return
		}

		if err := a.savePageSchema(r.Context(), pageID, "home", schema); err != nil {
			a.renderEditor(w, buildEditorView(schema, "Could not save changes."))
			return
		}

		http.Redirect(w, r, "/admin/editor", http.StatusSeeOther)
		return
	}

	if r.Method != http.MethodGet {
		http.Error(w, "method not allowed", http.StatusMethodNotAllowed)
		return
	}

	a.renderEditor(w, buildEditorView(schema, ""))
}

func (a *app) renderEditor(w http.ResponseWriter, data editorViewData) {
	if err := a.templates.ExecuteTemplate(w, "admin_editor.html", data); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
	}
}

func (a *app) fetchPageSchema(ctx context.Context, slug string) (int64, pageSchema, error) {
	var (
		id        int64
		schemaRaw string
		ps        pageSchema
	)

	err := a.db.QueryRowContext(ctx, "SELECT id, `schema` FROM pages WHERE slug = ? LIMIT 1", slug).Scan(&id, &schemaRaw)
	if err != nil {
		return 0, ps, err
	}

	if err := json.Unmarshal([]byte(schemaRaw), &ps); err != nil {
		return 0, ps, err
	}

	return id, ps, nil
}

func (a *app) savePageSchema(ctx context.Context, pageID int64, slug string, schema pageSchema) error {
	data, err := json.MarshalIndent(schema, "", "    ")
	if err != nil {
		return err
	}

	tx, err := a.db.BeginTx(ctx, nil)
	if err != nil {
		return err
	}
	defer tx.Rollback()

	if _, err := tx.ExecContext(ctx,
		"INSERT INTO page_versions (page_id, `schema`, note) VALUES (?, ?, ?)",
		pageID,
		string(data),
		"Auto save from visual editor",
	); err != nil {
		return err
	}

	if _, err := tx.ExecContext(ctx,
		"UPDATE pages SET `schema` = ?, updated_at = NOW() WHERE slug = ?",
		string(data),
		slug,
	); err != nil {
		return err
	}

	return tx.Commit()
}

func (a *app) applyEditorChanges(r *http.Request, schema *pageSchema) error {
	for i := range schema.Sections {
		sec := &schema.Sections[i]
		if sec.Data == nil {
			sec.Data = map[string]interface{}{}
		}

		switch sec.ID {
		case "hero":
			sec.Enabled = r.FormValue("hero_enabled") != ""
			sec.Data["title"] = strings.TrimSpace(r.FormValue("hero_title"))
			sec.Data["tagline"] = strings.TrimSpace(r.FormValue("hero_tagline"))

			filename, err := saveUploadedFile(r, "hero_photo", "hero")
			if err != nil {
				return err
			}
			if filename != "" {
				sec.Data["photo"] = filename
			}
		case "about":
			sec.Data["text"] = strings.TrimSpace(r.FormValue("about_text"))
		case "skills":
			sec.Data["items"] = parseSkills(r)
		case "projects":
			existing := parseProjectsFromSection(*sec)
			sec.Data["items"] = parseProjects(r, existing)
		case "vision":
			sec.Data["text"] = strings.TrimSpace(r.FormValue("vision_text"))
		case "contact":
			sec.Data["items"] = parseContacts(r)
		}
	}

	return nil
}

func parseSkills(r *http.Request) []string {
	indexes := []int{}
	values := map[int]string{}

	for key, vals := range r.MultipartForm.Value {
		matches := skillsFieldPattern.FindStringSubmatch(key)
		if len(matches) != 2 || len(vals) == 0 {
			continue
		}
		idx, err := strconv.Atoi(matches[1])
		if err != nil {
			continue
		}
		values[idx] = strings.TrimSpace(vals[0])
		indexes = append(indexes, idx)
	}

	sort.Ints(indexes)

	items := make([]string, 0, len(indexes))
	seen := map[int]bool{}
	for _, idx := range indexes {
		if seen[idx] {
			continue
		}
		seen[idx] = true
		if value := values[idx]; value != "" {
			items = append(items, value)
		}
	}

	return items
}

func parseProjects(r *http.Request, existing []projectRow) []map[string]interface{} {
	type draft struct {
		Title       string
		Description string
	}

	drafts := map[int]*draft{}
	indexes := []int{}

	for key, vals := range r.MultipartForm.Value {
		matches := projectFieldPattern.FindStringSubmatch(key)
		if len(matches) != 3 || len(vals) == 0 {
			continue
		}
		idx, err := strconv.Atoi(matches[1])
		if err != nil {
			continue
		}
		if drafts[idx] == nil {
			drafts[idx] = &draft{}
			indexes = append(indexes, idx)
		}
		value := strings.TrimSpace(vals[0])
		if matches[2] == "title" {
			drafts[idx].Title = value
		} else {
			drafts[idx].Description = value
		}
	}

	sort.Ints(indexes)
	seen := map[int]bool{}
	items := make([]map[string]interface{}, 0, len(indexes))

	for _, idx := range indexes {
		if seen[idx] {
			continue
		}
		seen[idx] = true
		entry := drafts[idx]
		if entry == nil || entry.Title == "" {
			continue
		}

		imageName := ""
		if idx < len(existing) {
			imageName = existing[idx].Image
		}

		filename, err := saveUploadedFile(r, fmt.Sprintf("project_image_%d", idx), fmt.Sprintf("project_%d", idx))
		if err == nil && filename != "" {
			imageName = filename
		}

		items = append(items, map[string]interface{}{
			"title":       entry.Title,
			"description": entry.Description,
			"image":       imageName,
		})
	}

	return items
}

func parseContacts(r *http.Request) []map[string]interface{} {
	type draft struct {
		Name  string
		Value string
	}

	drafts := map[int]*draft{}
	indexes := []int{}

	for key, vals := range r.MultipartForm.Value {
		matches := contactFieldPattern.FindStringSubmatch(key)
		if len(matches) != 3 || len(vals) == 0 {
			continue
		}
		idx, err := strconv.Atoi(matches[1])
		if err != nil {
			continue
		}
		if drafts[idx] == nil {
			drafts[idx] = &draft{}
			indexes = append(indexes, idx)
		}
		value := strings.TrimSpace(vals[0])
		if matches[2] == "name" {
			drafts[idx].Name = value
		} else {
			drafts[idx].Value = value
		}
	}

	sort.Ints(indexes)
	seen := map[int]bool{}
	items := make([]map[string]interface{}, 0, len(indexes))

	for _, idx := range indexes {
		if seen[idx] {
			continue
		}
		seen[idx] = true
		entry := drafts[idx]
		if entry == nil || entry.Name == "" {
			continue
		}
		items = append(items, map[string]interface{}{
			"name":  entry.Name,
			"value": entry.Value,
		})
	}

	return items
}

func saveUploadedFile(r *http.Request, fieldName, label string) (string, error) {
	file, header, err := r.FormFile(fieldName)
	if err != nil {
		if errors.Is(err, http.ErrMissingFile) {
			return "", nil
		}
		return "", err
	}
	defer file.Close()

	if err := os.MkdirAll("uploads", 0o755); err != nil {
		return "", err
	}

	ext := filepath.Ext(header.Filename)
	if ext == "" {
		ext = ".bin"
	}
	filename := fmt.Sprintf("%d_%s%s", time.Now().Unix(), sanitizeName(label), strings.ToLower(ext))
	dstPath := filepath.Join("uploads", filename)

	dst, err := os.Create(dstPath)
	if err != nil {
		return "", err
	}
	defer dst.Close()

	if _, err := io.Copy(dst, file); err != nil {
		return "", err
	}

	return filename, nil
}

func sanitizeName(value string) string {
	value = strings.ToLower(strings.TrimSpace(value))
	value = strings.ReplaceAll(value, " ", "_")
	value = regexp.MustCompile(`[^a-z0-9_]+`).ReplaceAllString(value, "")
	if value == "" {
		return "upload"
	}
	return value
}

func buildEditorView(schema pageSchema, errMsg string) editorViewData {
	view := editorViewData{
		Error:       errMsg,
		HeroEnabled: true,
	}

	for _, sec := range schema.Sections {
		switch sec.ID {
		case "hero":
			view.HeroTitle = stringValue(sec.Data["title"])
			view.HeroTagline = stringValue(sec.Data["tagline"])
			view.HeroPhoto = stringValue(sec.Data["photo"])
			view.HeroEnabled = sec.Enabled
		case "about":
			view.AboutText = stringValue(sec.Data["text"])
		case "vision":
			view.VisionText = stringValue(sec.Data["text"])
		case "skills":
			view.Skills = parseSkillsFromSection(sec)
		case "projects":
			view.Projects = parseProjectsFromSection(sec)
		case "contact":
			view.Contacts = parseContactsFromSection(sec)
		}
	}

	if len(view.Skills) == 0 {
		view.Skills = []skillRow{{Index: 0, Value: ""}}
	}
	if len(view.Projects) == 0 {
		view.Projects = []projectRow{{Index: 0}}
	}
	if len(view.Contacts) == 0 {
		view.Contacts = []contactRow{{Index: 0}}
	}

	return view
}

func parseSkillsFromSection(sec section) []skillRow {
	raw, ok := sec.Data["items"].([]interface{})
	if !ok {
		return nil
	}
	rows := make([]skillRow, 0, len(raw))
	for i, item := range raw {
		rows = append(rows, skillRow{Index: i, Value: stringValue(item)})
	}
	return rows
}

func parseProjectsFromSection(sec section) []projectRow {
	raw, ok := sec.Data["items"].([]interface{})
	if !ok {
		return nil
	}
	rows := make([]projectRow, 0, len(raw))
	for i, item := range raw {
		m, ok := item.(map[string]interface{})
		if !ok {
			continue
		}
		rows = append(rows, projectRow{
			Index:       i,
			Title:       stringValue(m["title"]),
			Description: stringValue(m["description"]),
			Image:       stringValue(m["image"]),
		})
	}
	return rows
}

func parseContactsFromSection(sec section) []contactRow {
	raw, ok := sec.Data["items"].([]interface{})
	if !ok {
		return nil
	}
	rows := make([]contactRow, 0, len(raw))
	for i, item := range raw {
		m, ok := item.(map[string]interface{})
		if !ok {
			continue
		}
		rows = append(rows, contactRow{
			Index: i,
			Name:  stringValue(m["name"]),
			Value: stringValue(m["value"]),
		})
	}
	return rows
}

func stringValue(v interface{}) string {
	switch value := v.(type) {
	case string:
		return value
	default:
		return ""
	}
}

func (a *app) setAdminCookie(w http.ResponseWriter) {
	mac := hmac.New(sha256.New, []byte(a.cfg.AppSecret))
	mac.Write([]byte("admin"))
	sig := base64.RawURLEncoding.EncodeToString(mac.Sum(nil))

	http.SetCookie(w, &http.Cookie{
		Name:     adminCookieName,
		Value:    "admin." + sig,
		Path:     "/",
		HttpOnly: true,
		SameSite: http.SameSiteLaxMode,
		MaxAge:   7 * 24 * 60 * 60,
	})
}

func (a *app) isAdmin(r *http.Request) bool {
	cookie, err := r.Cookie(adminCookieName)
	if err != nil {
		return false
	}

	parts := strings.Split(cookie.Value, ".")
	if len(parts) != 2 || parts[0] != "admin" {
		return false
	}

	mac := hmac.New(sha256.New, []byte(a.cfg.AppSecret))
	mac.Write([]byte("admin"))
	expected := base64.RawURLEncoding.EncodeToString(mac.Sum(nil))
	return hmac.Equal([]byte(parts[1]), []byte(expected))
}
