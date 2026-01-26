CREATE DATABASE portfolio;
USE portfolio;


CREATE TABLE admin_users (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(50) UNIQUE,
password VARCHAR(255)
);


CREATE TABLE skills (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100)
);


CREATE TABLE projects (
id INT AUTO_INCREMENT PRIMARY KEY,
title VARCHAR(200),
description TEXT
);

CREATE TABLE site_content (
  id INT PRIMARY KEY,
  about TEXT,
  vision TEXT,
  email VARCHAR(255),
  github VARCHAR(255),
  youtube VARCHAR(255)
);

