CREATE TABLE bookmarks (
    id INTEGER AUTO_INCREMENT,
    url TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id)
);

CREATE TABLE comments (
    id INTEGER AUTO_INCREMENT,
    bookmark_id INTEGER NOT NULL,
    text TEXT NOT NULL,
    ip VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT NOW(),
    PRIMARY KEY (id),
    FOREIGN KEY (bookmark_id) REFERENCES bookmarks(id) ON DELETE CASCADE
);


