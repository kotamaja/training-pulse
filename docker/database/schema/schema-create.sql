CREATE TABLE athlete
(
    id                 INT AUTO_INCREMENT NOT NULL,
    public_id          VARCHAR(26)        NOT NULL,
    display_name       VARCHAR(180)       NOT NULL,
    birth_year         INT              DEFAULT NULL,
    height_cm          DOUBLE PRECISION DEFAULT NULL,
    weight_kg          DOUBLE PRECISION DEFAULT NULL,
    resting_heart_rate INT              DEFAULT NULL,
    max_heart_rate     INT              DEFAULT NULL,
    ftp_watts          INT              DEFAULT NULL,
    created_at         DATETIME           NOT NULL,
    updated_at         DATETIME         DEFAULT NULL,
    user_id            INT                NOT NULL,
    UNIQUE INDEX uniq_athlete__public_id (public_id),
    UNIQUE INDEX uniq_athlete__user_id (user_id),
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4
  COLLATE `utf8mb4_unicode_ci`;

CREATE TABLE athlete_external_account
(
    id                      INT AUTO_INCREMENT NOT NULL,
    public_id               VARCHAR(26)        NOT NULL,
    provider                VARCHAR(255)       NOT NULL,
    provider_account_id     VARCHAR(255)       NOT NULL,
    display_name            VARCHAR(255) DEFAULT NULL,
    access_token            LONGTEXT     DEFAULT NULL,
    refresh_token           LONGTEXT     DEFAULT NULL,
    expires_at              DATETIME     DEFAULT NULL,
    scopes                  JSON               NOT NULL,
    status                  VARCHAR(255)       NOT NULL,
    last_sync_at            DATETIME     DEFAULT NULL,
    last_successful_sync_at DATETIME     DEFAULT NULL,
    last_error_at           DATETIME     DEFAULT NULL,
    last_error_message      LONGTEXT     DEFAULT NULL,
    created_at              DATETIME           NOT NULL,
    updated_at              DATETIME     DEFAULT NULL,
    athlete_id              INT                NOT NULL,
    INDEX idx_athlete_external_account__athlete (athlete_id),
    UNIQUE INDEX uniq_athlete_external_account__athlete_provider_account (athlete_id, provider, provider_account_id),
    UNIQUE INDEX uniq_athlete_external_account__public_id (public_id),
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4
  COLLATE `utf8mb4_unicode_ci`;

CREATE TABLE training_activity
(
    id                   INT AUTO_INCREMENT NOT NULL,
    public_id            VARCHAR(26)        NOT NULL,
    source               VARCHAR(255)       NOT NULL,
    external_id          VARCHAR(255)       NOT NULL,
    name                 VARCHAR(512)       NOT NULL,
    sport_type           VARCHAR(255)       NOT NULL,
    started_at           DATETIME           NOT NULL,
    started_at_local     DATETIME         DEFAULT NULL,
    timezone             VARCHAR(100)     DEFAULT NULL,
    distance_m           DOUBLE PRECISION DEFAULT NULL,
    moving_time_s        INT              DEFAULT NULL,
    elapsed_time_s       INT              DEFAULT NULL,
    elevation_gain_m     DOUBLE PRECISION DEFAULT NULL,
    average_speed_mps    DOUBLE PRECISION DEFAULT NULL,
    max_speed_mps        DOUBLE PRECISION DEFAULT NULL,
    average_heartrate    DOUBLE PRECISION DEFAULT NULL,
    max_heartrate        DOUBLE PRECISION DEFAULT NULL,
    average_watts        DOUBLE PRECISION DEFAULT NULL,
    max_watts            DOUBLE PRECISION DEFAULT NULL,
    calories             DOUBLE PRECISION DEFAULT NULL,
    summary_polyline     LONGTEXT         DEFAULT NULL,
    route                LINESTRING       DEFAULT NULL,
    raw_external_summary JSON             DEFAULT NULL,
    raw_external_detail  JSON             DEFAULT NULL,
    synced_at            DATETIME         DEFAULT NULL,
    created_at           DATETIME           NOT NULL,
    updated_at           DATETIME         DEFAULT NULL,
    streams_synced_at    DATETIME         DEFAULT NULL,
    athlete_id           INT                NOT NULL,
    INDEX idx_training_activity__athlete (athlete_id),
    INDEX idx_training_activity__athlete_started_at (athlete_id, started_at),
    INDEX idx_training_activity__sport_type (sport_type),
    UNIQUE INDEX uniq_training_activity__public_id (public_id),
    UNIQUE INDEX uniq_training_activity__athlete_source_external_id (athlete_id, source, external_id),
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4
  COLLATE `utf8mb4_unicode_ci`;

CREATE TABLE app_user
(
    id         INT AUTO_INCREMENT NOT NULL,
    public_id  VARCHAR(26)        NOT NULL,
    email      VARCHAR(180)       NOT NULL,
    username   VARCHAR(180)       NOT NULL,
    roles      JSON               NOT NULL,
    enabled    TINYINT(1)         NOT NULL,
    password   VARCHAR(255) DEFAULT NULL,
    created_at DATETIME           NOT NULL,
    updated_at DATETIME     DEFAULT NULL,
    UNIQUE INDEX uniq_app_user__public_id (public_id),
    UNIQUE INDEX uniq_app_user__email (email),
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4
  COLLATE `utf8mb4_unicode_ci`;

CREATE TABLE refresh_token
(
    id           INT AUTO_INCREMENT NOT NULL,
    token_hash   VARCHAR(64)        NOT NULL,
    mode         VARCHAR(255)       NOT NULL,
    expires_at   DATETIME           NOT NULL,
    created_at   DATETIME           NOT NULL,
    last_used_at DATETIME DEFAULT NULL,
    revoked_at   DATETIME DEFAULT NULL,
    user_id      INT                NOT NULL,
    INDEX idx_refresh_token__user_id (user_id),
    INDEX idx_refresh_token__expires_at (expires_at),
    UNIQUE INDEX uniq_refresh_token__token_hash (token_hash),
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4
  COLLATE `utf8mb4_unicode_ci`;


ALTER TABLE athlete
    ADD CONSTRAINT FK_C03B8321A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE;
ALTER TABLE athlete_external_account
    ADD CONSTRAINT FK_1FC45D0FE6BCB8B FOREIGN KEY (athlete_id) REFERENCES athlete (id) ON DELETE CASCADE;
ALTER TABLE refresh_token
    ADD CONSTRAINT FK_C74F2195A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE;
ALTER TABLE training_activity
    ADD CONSTRAINT FK_4B111ACDFE6BCB8B FOREIGN KEY (athlete_id) REFERENCES athlete (id) ON DELETE CASCADE;

