INSERT INTO "user"
(email, roles, password, first_name, last_name, is_active, created_at, updated_at, company_id)
VALUES
('anna.kowalska@balticoffice.pl', '["ROLE_CLIENT"]', '$2y$13$FkYm0N5NceeGaRlg0aiiauu0KWIMw7J9b5QgUy5Yi3jwTy5Yqbqzm', 'Anna', 'Kowalska', true, CURRENT_TIMESTAMP, NULL, 3),
('piotr.nowak@balticoffice.pl', '["ROLE_CLIENT"]', '$2y$13$FkYm0N5NceeGaRlg0aiiauu0KWIMw7J9b5QgUy5Yi3jwTy5Yqbqzm', 'Piotr', 'Nowak', true, CURRENT_TIMESTAMP, NULL, 3),
('marta.wisniewska@balticoffice.pl', '["ROLE_CLIENT"]', '$2y$13$FkYm0N5NceeGaRlg0aiiauu0KWIMw7J9b5QgUy5Yi3jwTy5Yqbqzm', 'Marta', 'Wiœniewska', true, CURRENT_TIMESTAMP, NULL, 3);
