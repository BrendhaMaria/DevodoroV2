CREATE database devodoro;
USE devodoro;

-- =========================
-- EMPRESA
-- =========================
CREATE TABLE empresa (
  id_empresa INT NOT NULL AUTO_INCREMENT,
  nome VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_empresa)
);

-- =========================
-- FUNCIONARIO (mantido para evolução futura)
-- =========================
CREATE TABLE funcionario (
  id_funcionario INT NOT NULL AUTO_INCREMENT,
  nome VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  tipo_usuario VARCHAR(20) DEFAULT 'FUNCIONARIO',
  ativo TINYINT(1) DEFAULT 1,
  id_empresa INT,
  PRIMARY KEY (id_funcionario),
  FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa)
);

-- =========================
-- TAREFAS (NÚCLEO ATUAL DO SISTEMA)
-- =========================
CREATE TABLE tarefas (
  id_tarefa INT NOT NULL AUTO_INCREMENT,
  titulo VARCHAR(200) NOT NULL,
  prioridade VARCHAR(20) DEFAULT 'MEDIA',
  status VARCHAR(20) DEFAULT 'PENDENTE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_tarefa)
);

CREATE TABLE tarefa_funcionario (
  id_tarefa INT NOT NULL,
  id_funcionario INT NOT NULL,
  PRIMARY KEY (id_tarefa, id_funcionario),
  FOREIGN KEY (id_tarefa) REFERENCES tarefas(id_tarefa) ON DELETE CASCADE,
  FOREIGN KEY (id_funcionario) REFERENCES funcionario(id_funcionario) ON DELETE CASCADE
);