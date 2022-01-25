# roistat
Тестовое задание

Для запуска внутри Docker-контейнера:
  1. В файле hosts прописать "127.0.0.1 roistat.xip.io" без кавычек
  2. docker-compose up -d --build (Пункты 1 и 2 нужны только в первый раз для запуска)
  3. docker exec -ti roistat bash
  4. php parser.php "*Путь к файлу*"

Для альтернативного запуска:
  1. php parser.php "*Путь к файлу*"
