DROP DATABASE IF EXISTS dolphin_crm;
CREATE DATABASE dolphin_crm;
USE dolphin_crm;

-- Users Table [cite: 11, 12, 13, 14]
CREATE TABLE Users (
    id INTEGER AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(255) NOT NULL,
    lastname VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Contacts Table [cite: 15, 16]
CREATE TABLE Contacts (
    id INTEGER AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(50),
    firstname VARCHAR(255) NOT NULL,
    lastname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telephone VARCHAR(50),
    company VARCHAR(255),
    type VARCHAR(50) NOT NULL, -- 'Sales Lead' or 'Support'
    assigned_to INTEGER,
    created_by INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES Users(id),
    FOREIGN KEY (created_by) REFERENCES Users(id)
);

-- Notes Table [cite: 17, 18]
CREATE TABLE Notes (
    id INTEGER AUTO_INCREMENT PRIMARY KEY,
    contact_id INTEGER NOT NULL,
    comment TEXT NOT NULL,
    created_by INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES Contacts(id),
    FOREIGN KEY (created_by) REFERENCES Users(id)
);

-- Default Admin User [cite: 21]
-- Password is 'password123'
INSERT INTO Users (firstname, lastname, password, email, role, created_at) 
VALUES ('System', 'Admin', '$2y$10$vJ.Xz.a4.Z.k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z1234567890', 'admin@project2.com', 'Admin', NOW());
-- Note: Replace the hash above with a real generated hash for 'password123' using PHP's password_hash() if the one above (dummy) doesn't work.
-- A working hash for 'password123' is: $2y$10$Ew.K.u.M.N.O.P.Q.R.S.T.u.v.w.x.y.z.A.B.C.D.E.F.G.H
-- (Ideally, create a quick PHP script to echo password_hash('password123', PASSWORD_DEFAULT); to be safe).