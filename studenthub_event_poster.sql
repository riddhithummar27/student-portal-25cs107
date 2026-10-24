USE studenthub;

-- Run this only once. If events.poster_path already exists, skip this statement.
ALTER TABLE events ADD COLUMN poster_path VARCHAR(255) NULL;
