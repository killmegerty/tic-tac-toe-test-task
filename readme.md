# Пререквизиты

Из корня проекта, установка зависимостей
```sh
$ composer install
$ npm install
```
Дать полные права на кеш\лог папки для ларавела
```sh
$ sudo chmod 777 -R storage/
$ sudo chmod 777 -R bootstrap/cache/
```
Создание БД
```sh
mysql> CREATE DATABASE minenko_konstantin DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;
mysql> GRANT ALL PRIVILEGES ON minenko_konstantin.* TO minenko_konstantin@localhost IDENTIFIED BY 'minenko_konstantin';
```
Миграции, из корня проекта
```sh
php artisan migrate:fresh
```

# Комментарии по проекту
Из .gitignore .env я убрал, он есть в проекте. Сделал специально, чтобы ускорить настройку окружения.
Фронт - Vue, из коробки Laravel'a. resources/assets/app.js
Драйвер для реалицации бродкаста использовал Pusher.

# Инструкция \ Описание логики работы
route - описание

`/` - меню с выбором режима игры - против компьютера(vs AI) или против человека(vs Human). У "vs Human" 2 пункта подменю - создать игру или подключиться к существующией по ID игры. Кнопки в меню выполняют исключительно функции редиректа на другие URLы, то есть можно и без них по конкретным URL'ам создавать\заходить в игру.

`/game-ai` - игра против компьютера, после прохода по этому URL'у в БД сервера создается игра с таким же GameSessionUUID как и на экране , в таблице games . По клику на игровую доску в БД сервера, таблицу games_history записывается ход, так же, в случае с игрой против компьютера - записывается еще один ход, ход копьютера. Стратегия копьютера - случайно ходить по игровой доске, любое свободное поле. После завершения игры, в таблице games запишется status, результат игры, если игра не завершена status == null.

`/game-human` - игра против человека. После прохода по URL'у - на сервере в БД т.games создается игра. Ходы хоста и оппонента записываются в таблицу games_history. Человек который проходит по URL /game-human является хостом, он должен пригласить опоннета используя gameSessionUUID . По окончанию игры если нажать на Restart кнопку - приглашать опоннета нужно заного! На экране помимо доски следующая информация:
- gameSessionUUID для экрана /join, можно вставить в инпут, по сабмиту средиректит на текущую игру
- QR код, для телефона, чтобы полную ссылку в браузер вставить и зайти в тек.игру
- прямая ссылка
- отображение роли игрока // host , opponent
- кто сейчас ходит // host , opponent

`/game-human/<gameSessionUuid>` - если игра существует и в ней уже зарегистрировано не более 1 хода (ход хоста) - откроется игра с хостом. Человек , который проходит по `/game-human/<gameSessionUuid>` - оппонент. Логика аналогична с `/game-human`

`/join` - экран для оппонента, для перехода на `/game-human/<gameSessionUuid>` путем передачи в инпут gameSessionUuid, полученного от хоста.
