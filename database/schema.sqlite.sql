-- Schéma Bilibili pour SQLite

CREATE TABLE IF NOT EXISTS utilisateurs (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    pseudo        VARCHAR(20)  NOT NULL UNIQUE,
    email         VARCHAR(190) NOT NULL UNIQUE,
    mot_de_passe  VARCHAR(255) NOT NULL,
    role          VARCHAR(10)  NOT NULL DEFAULT 'membre',
    cree_le       DATETIME     NOT NULL
);

CREATE TABLE IF NOT EXISTS series (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    slug      VARCHAR(120) NOT NULL UNIQUE,
    titre     VARCHAR(120) NOT NULL,
    synopsis  TEXT         NOT NULL,
    genre     VARCHAR(40)  NOT NULL,
    annee     INTEGER      NOT NULL,
    statut    VARCHAR(20)  NOT NULL DEFAULT 'En cours',
    teinte    INTEGER      NOT NULL DEFAULT 220,
    cree_le   DATETIME     NOT NULL
);

CREATE TABLE IF NOT EXISTS episodes (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    serie_id   INTEGER      NOT NULL REFERENCES series(id) ON DELETE CASCADE,
    saison     INTEGER      NOT NULL,
    numero     INTEGER      NOT NULL,
    titre      VARCHAR(150) NOT NULL,
    duree_min  INTEGER      NOT NULL DEFAULT 24,
    UNIQUE (serie_id, saison, numero)
);

CREATE TABLE IF NOT EXISTS planning (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    serie_id  INTEGER     NOT NULL REFERENCES series(id) ON DELETE CASCADE,
    jour      INTEGER     NOT NULL CHECK (jour BETWEEN 1 AND 7),
    heure     VARCHAR(5)  NOT NULL,
    libelle   VARCHAR(80) NOT NULL DEFAULT 'Nouvel épisode'
);

CREATE TABLE IF NOT EXISTS favoris (
    utilisateur_id INTEGER  NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    serie_id       INTEGER  NOT NULL REFERENCES series(id) ON DELETE CASCADE,
    ajoute_le      DATETIME NOT NULL,
    PRIMARY KEY (utilisateur_id, serie_id)
);

CREATE TABLE IF NOT EXISTS visionnages (
    utilisateur_id INTEGER  NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    episode_id     INTEGER  NOT NULL REFERENCES episodes(id) ON DELETE CASCADE,
    vu_le          DATETIME NOT NULL,
    PRIMARY KEY (utilisateur_id, episode_id)
);

CREATE INDEX IF NOT EXISTS idx_episodes_serie ON episodes(serie_id);
CREATE INDEX IF NOT EXISTS idx_planning_jour  ON planning(jour, heure);
