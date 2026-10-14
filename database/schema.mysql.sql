-- Schéma Bilibili pour MySQL / MariaDB

CREATE TABLE IF NOT EXISTS utilisateurs (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pseudo        VARCHAR(20)  NOT NULL UNIQUE,
    email         VARCHAR(190) NOT NULL UNIQUE,
    mot_de_passe  VARCHAR(255) NOT NULL,
    role          VARCHAR(10)  NOT NULL DEFAULT 'membre',
    cree_le       DATETIME     NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS series (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug      VARCHAR(120) NOT NULL UNIQUE,
    titre     VARCHAR(120) NOT NULL,
    synopsis  TEXT         NOT NULL,
    genre     VARCHAR(40)  NOT NULL,
    annee     SMALLINT     NOT NULL,
    statut    VARCHAR(20)  NOT NULL DEFAULT 'En cours',
    teinte    SMALLINT     NOT NULL DEFAULT 220,
    cree_le   DATETIME     NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS episodes (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    serie_id   INT UNSIGNED NOT NULL,
    saison     SMALLINT     NOT NULL,
    numero     SMALLINT     NOT NULL,
    titre      VARCHAR(150) NOT NULL,
    duree_min  SMALLINT     NOT NULL DEFAULT 24,
    UNIQUE KEY uq_episode (serie_id, saison, numero),
    CONSTRAINT fk_episode_serie FOREIGN KEY (serie_id) REFERENCES series(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS planning (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    serie_id  INT UNSIGNED NOT NULL,
    jour      TINYINT      NOT NULL,
    heure     VARCHAR(5)   NOT NULL,
    libelle   VARCHAR(80)  NOT NULL DEFAULT 'Nouvel épisode',
    KEY idx_planning_jour (jour, heure),
    CONSTRAINT fk_planning_serie FOREIGN KEY (serie_id) REFERENCES series(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS favoris (
    utilisateur_id INT UNSIGNED NOT NULL,
    serie_id       INT UNSIGNED NOT NULL,
    ajoute_le      DATETIME     NOT NULL,
    PRIMARY KEY (utilisateur_id, serie_id),
    CONSTRAINT fk_favori_user  FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT fk_favori_serie FOREIGN KEY (serie_id) REFERENCES series(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS visionnages (
    utilisateur_id INT UNSIGNED NOT NULL,
    episode_id     INT UNSIGNED NOT NULL,
    vu_le          DATETIME     NOT NULL,
    PRIMARY KEY (utilisateur_id, episode_id),
    CONSTRAINT fk_vue_user    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT fk_vue_episode FOREIGN KEY (episode_id) REFERENCES episodes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
