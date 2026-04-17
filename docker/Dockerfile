FROM golang:1.25-alpine AS builder

WORKDIR /src

COPY go.mod go.sum ./
RUN go mod download

COPY . ./

RUN CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o /bin/portfolio-cms .

FROM alpine:3.21

WORKDIR /app

COPY --from=builder /bin/portfolio-cms /app/portfolio-cms
COPY public /app/public
COPY templates /app/templates
COPY uploads /app/uploads

RUN mkdir -p /app/uploads

EXPOSE 8080

CMD ["/app/portfolio-cms"]
