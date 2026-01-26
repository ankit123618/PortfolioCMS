<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ankit Sharma | Software Engineer & Researcher</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Palanquin+Dark:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #0b0b0f;
            --card: #13131a;
            --red: #e10600;
            --text: #eaeaea;
            --muted: #9a9a9a;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }

        header {
            padding: 80px 10%;
            background: radial-gradient(circle at top left, rgba(225, 6, 0, 0.25), transparent 60%);
        }

        header h1 {
            font-family: 'Palanquin Dark', sans-serif;
            font-size: 3rem;
            color: var(--red);
        }

        header p {
            max-width: 600px;
            margin-top: 15px;
            color: var(--muted);
        }

        nav {
            margin-top: 30px;
        }

        nav a {
            color: var(--text);
            margin-right: 20px;
            text-decoration: none;
            font-weight: 500;
            position: relative;
        }

        nav a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: var(--red);
            bottom: -4px;
            left: 0;
            transition: width 0.3s;
        }

        nav a:hover::after {
            width: 100%;
        }

        section {
            padding: 70px 10%;
        }

        h2 {
            font-family: 'Palanquin Dark', sans-serif;
            font-size: 2.2rem;
            margin-bottom: 30px;
            color: var(--red);
        }

        .card {
            background: var(--card);
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .skills {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .skill {
            background: #1b1b24;
            border-radius: 10px;
            min-height: 48px;
            /* adjust once, then forget */
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 16px;
            
        }

        /* .skill:hover {
            background: var(--red);
            color: #fff;
            cursor: default;
            transition: background 0.3s, color 0.3s;
        } */

        .projects .card h3 {
            color: #fff;
            margin-bottom: 10px;
        }

        .projects .card p {
            color: var(--muted);
            font-size: 0.95rem;
        }

        footer {
            padding: 40px 10%;
            text-align: center;
            color: var(--muted);
            font-size: 0.9rem;
            border-top: 1px solid #1f1f2a;
        }

        @media (max-width: 768px) {
            header h1 {
                font-size: 2.3rem;
            }

            h2 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>

<body>

    <header>
        <h1>Ankit Sharma</h1>
        <p>
            Software Engineer • Researcher • Educator
            <br>
            Building technology, knowledge systems, and tools that remove superstition and empower minds.
        </p>

        <nav>
            <a href="#about">About</a>
            <a href="#skills">Skills</a>
            <a href="#projects">Projects</a>
            <a href="#vision">Vision</a>
            <a href="#contact">Contact</a>
        </nav>
    </header>
    <section id="about">
        <h2>About Me</h2>
        <div class="card" id="about-text">
            <!-- <p>
        I am a software engineer and researcher passionate about building
        technologies and knowledge systems that empower logical thinking
        and scientific temperament. My work spans web development, simulations,
        and educational platforms aimed at demystifying complex concepts.
      </p> -->

        </div>
    </section>

    <section id="skills">
        <h2>Skills</h2>
        <div class="skills" id="skills-list">
            <!-- <div class="skill">C / C++</div>
      <div class="skill">Python</div>
      <div class="skill">Golang</div>
      <div class="skill">PHP</div>
      <div class="skill">JavaScript</div>
      <div class="skill">Linux (Fedora)</div>
      <div class="skill">DSA</div>
      <div class="skill">MySQL</div>
      <div class="skill">System Design</div>
      <div class="skill">Research & R&D</div> -->
        </div>
    </section>

    <section id="projects" class="projects">
        <h2>Projects</h2>

        <div class="card" id="projects-list">

        </div>
    </section>

    <section id="vision">
        <h2>Vision</h2>
        <div class="card" id="vision-text">
            <!-- <p>
        My long-term vision is to build libraries, research centers, and technologies
        that make humanity richer in knowledge, logical thinking, and scientific temperament.
        Technology is my tool — clarity is my goal.
      </p> -->
        </div>
    </section>

    <section id="contacts">
        <h2>Contact</h2>
        <div class="card" id="contact">
            <!-- <p>Email: <strong>your-email@example.com</strong></p>
      <p>GitHub: <strong>github.com/yourusername</strong></p>
      <p>YouTube: <strong>Educational Channel</strong></p> -->
        </div>
    </section>


    <footer>
        © 2026 Ankit Sharma • Built on Linux with clarity and purpose
    </footer>

    <script>
        fetch('api/get_skills.php')
            .then(r => r.json())
            .then(data => {
                document.getElementById('skills-list').innerHTML =
                    data.map(s => `<div class="skill"><p>${s.name}</p></div>`).join('');
            });

        fetch('api/get_projects.php')
            .then(r => r.json())
            .then(data => {
                document.getElementById('projects-list').innerHTML =
                    data.map(p => `
        <div class="card">
          ${p.image ? `<img src="/../uploads/${p.image}" style="width:100%;border-radius:12px;margin-bottom:10px;">` : ''}
          <h3>${p.title}</h3>
          <p>${p.description}</p>
        </div>
      `).join('');
            });
    </script>
    <script>
        fetch('api/get_content.php')
            .then(r => r.json())
            .then(d => {
                document.getElementById('about-text').innerHTML = '<p>' + d.about + '</p>';
                document.getElementById('vision-text').innerHTML = '<p>' + d.vision + '</p>';
                document.getElementById('contact').innerHTML = `
                    <div class="card">
                        <p>Email: <strong>${d.email}</strong></p>
                        <p>GitHub: <strong>${d.github}</strong></p>
                        <p>YouTube: <strong>${d.youtube}</strong></p>
                    </div>
                `;
            });
    </script>


</body>

</html>