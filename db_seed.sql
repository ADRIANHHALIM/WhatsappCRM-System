-- PostgreSQL DB Seed for WhatsApp CRM
-- Password used for both accounts: admin123
-- The password hash uses bcrypt ($2y$10$...) as recommended for secure PHP applications.

INSERT INTO employees (fullname, username, password, role, is_active)
VALUES 
(
  'Ka Roseuphoria', 
  'owner', 
  '$2y$10$bOOWx7i4iB5B/X0J0sI51e4Nq/hA48xW2wL57QpZ/FOf9X4x45m5G', 
  'owner', 
  TRUE
),
(
  'Adrian', 
  'adrian', 
  '$2y$10$bOOWx7i4iB5B/X0J0sI51e4Nq/hA48xW2wL57QpZ/FOf9X4x45m5G', 
  'staff', 
  TRUE
);
