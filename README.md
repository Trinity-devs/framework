# Микрофреймворк Trinity

Индекс проекта: `TRNTYFRMWR`

## Требования к окружению

* PHP >= 8.2


### Миграции

Для вызова команд необходимо прописать:

```
php ./vendor/bin/phinx 
```

- Команды:
    - `init` - Инициализировать приложение для Phinx
    - `create` - Создать новую миграцию
    - `migrate` - Миграция базы данных
    - `rollback` - Откат к последней или к определенной миграции
    - `test` - Проверить конфигурационный файл
    - `list` - Перечислить команды
    - `breakpoint` - Управление брэйкпоинтами
    - `completion` - Выгрузить сценарий завершения оболочки
    - `help` - Отображение справки по команде
    - `status` - Показать статус миграции
