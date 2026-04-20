-- PostgreSQL DB Seed for WhatsApp CRM
-- Password used for both accounts: admin123
-- The password hash uses bcrypt ($2y$10$...) as recommended for secure PHP applications.

INSERT INTO employees (fullname, username, password, role, is_active)
VALUES 
(
  'Ka Roseuphoria', 
  'owner', 
  '$2y$12$D.347Bhgl4rrPpJZ67wkyu4SSyIQ7SUs7DBeCLTl3.VlMSLkAd2hm', 
  'owner', 
  TRUE
),
(
  'Adrian', 
  'adrian', 
  '$2y$12$D.347Bhgl4rrPpJZ67wkyu4SSyIQ7SUs7DBeCLTl3.VlMSLkAd2hm', 
  'staff', 
  TRUE
);
