# Плагин «Курсовая работа» для Moodle

Плагин позволяет автоматизировать процесс выбора темы и руководителя курсовой работы.

После выбора темы студент может общаться с руководителем во встроенном чате, обмениваться файлами, отправлять работу на проверку.

В курсовой работе можно оставить методические указания по выполнению работы.

Также есть ограниченный функционал по выдаче задания к курсовой работе. 
Развитие этого функционала не планируется.

## Версия Moodle и тестирование плагина

Недавно большая часть кода была полностью переписана.

Основной функционал был протестирован на версии Moodle 3.10.3.

Теоретически должен работать с версиями Moodle начиная с 3.5.

С сентября 2022 начнётся работа/тестирование модуля с реальными пользователями,
после значительной переписи кода.

## Работоспособность
Данный плагин используется в Белорусском государственном экономическом универстите с 1 сентября 2018 года. Работает стабильно, возникающие баги устраняются оперативно.

## Установка
1. скачать zip архив с github (кнопка Код)
3. перейти на страницу установки плагинов moodle
4. загрузить zip архив
5. следовать инструкциям moodle

## Использование

Добавление экземпляра курсовой работы в курс аналогично добавлению любой другой активности, например, книги или теста.

После добавления экземпляра курсовой обязательно нужно определить хотя бы одного руководителя. Для этого нужно перейти на страницу определения руководителей курсовой работы. Ссылка на неё отображается при входе в курсовую, если руководители ещё не определены.

Остальные настройки дополнительны, например, распределение студентов к руководителям или использование коллекции предлагаемых тем.

*В блоке Настройка есть разделы: **настройка** и **поддержка**.*

В разделе *Настройки* можно: определить руководителей, распределить студентов к руководителям, установить шаблон задания, выдаваемый по умолчанию, и определить темы, предлагаемые студентам.

В разделе *Поддержка* можно: вернуть работу студента на этап выбора темы, вернуть работу на доработку, заменить руководителя и удалить работу студента.

В блоке Настройка в разделе Управление шаблонами заданий можно создать задания или изменить их.

В блоке Настройка в разделе Управление коллекциями можно создать и изменить коллекции тем.

В блоке Настройка в разделе Обзор квоты руководителей можно удобно контролировать квоту руководителей.

## Дальнейшие планы
* Поиск и исправление багов.

**Функционал, связанный с разделами курсовой работы, в ближайшее время не будет развиваться.**

## Используемые компоненты

Intro.js v4.3.0 Copyright (C) 2012-2021 Afshin Mehrabani (afshin.meh@gmail.com)

## Автор
Yan Lidski (Reiko)

## Apache-2.0 License 
