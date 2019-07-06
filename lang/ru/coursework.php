<?php

// General strings
$string['pluginname'] = 'Курсовая работа';
$string['modulename'] = 'Курсовая работа';
$string['modulenameplural'] = 'Курсовые работы';
$string['name'] = 'Название';
$string['intro'] = 'Вступление';
$string['pluginadministration'] = 'Управление курсовой работой';

// Coursework configuration
$string['coursework_configuration'] = 'Настройка курсовой работы';
$string['participants_management'] = 'Управление участниками';
$string['theme'] = 'Тематика';
$string['themes_management'] = 'Управление тематикой';
$string['no_available_themes'] = 'Нет доступных тем';
$string['use_own_theme'] = 'Использовать собственную тему';

// Enrollment strings
$string['configurate_coursework'] = 'Настройка курсовой работы';
$string['select_groups'] = 'Выберите группы, студенты которых будут участвовать в данной курсовой работе';
$string['quota_left'] = 'Необходимо распределить квот: ';
$string['add_tutor'] = 'Добавить преподавателя';
$string['no_permission'] = 'У вас нету разрешения просматривать эту страницу';
$string['save_changes'] = 'Сохранить изменения';
$string['delete'] = 'Удалить';

// Themes management
$string['coursework_themes_management'] = 'Управление тематикой курсовых работ';
$string['add_new_theme'] = 'Добавить новую тему';
$string['edit'] = 'Редактировать';

// Students assignment
$string['students_assignment'] = 'Распределение студентов';
$string['students_assignment_header'] = 'Распределение студентов по элементам курсовой работы';
$string['group_assignment'] = 'Групповое распределение';
$string['no_assign'] = 'Не назначено';

// View strings
$string['fullname'] = 'ФИО';
$string['group'] = 'Группа';
$string['leader'] = 'Руководитель';
$string['course'] = 'Курс';
$string['quota'] = 'Квота';
$string['grade'] = 'Пред. оценка';
$string['comment'] = 'Комментарий';
$string['choose'] = 'Выбрать';
$string['grade_student'] = 'Оценить студента';
$string['remove_selection'] = 'Отменить выбор';
$string['cant_be_undone'] = 'Сделанный выбор невозможно самостоятельно отменить';
$string['back_to_course'] = 'Вернуться к курсу';
$string['not_selected'] = 'Не выбран(а)';
$string['not_available'] = 'Недоступен(на)';

// Errors strings
$string['e:missing-coursework-student-record'] = 'Ошибка: отсутствует запись студента курсовой работы.';
$string['e:missing-user-record'] = 'Ошибка: отсутствует запись пользователя.';
$string['e:missing-student-record-id'] = 'Ошибка: отсутствует id записи, удаляемого студента.';
$string['e:missing-student-id'] = 'Ошибка: отсутствует id студента. Почтовое уведомление не будет отпавлено.';
$string['e:missing-grade-and-comment'] = 'Ошибка: отсутствует новая оценка и новый комментарий.';
$string['e:student-not-deleted'] = 'Ошибка базы данных: запись студента курсовой работы не удалёна.';
$string['e:student-not-updated'] = 'Ошибка базы данных: запись студента курсовой работы не обновлена.';
$string['e:ins:student-not-selected'] = 'Ошибка базы данных: не создана запись студента с выбранной темой.';
$string['e:upd:student-not-selected'] = 'Ошибка базы данных: не обновлена запись студента с выбранной темой.';
$string['e:missing-student-id'] = 'Ошибка: отсутствует id студента.';
$string['e:missing-tutor-id'] = 'Ошибка: отсутствует id преподавателя.';
$string['e:missing-course-id'] = 'Ошибка: отсутствует id курса.';
$string['e:missing-theme-and-owntheme'] = 'Ошибка: отсутствует id темы и собственная тема.';
$string['e:theme-already-used'] = 'Ошибка: Данная тема уже используется другим студентом.';
$string['e:tutor-quota-over'] = 'Ошибка: Квота на выбранную связку преподаватель + курс исчерана.';
$string['e:missing-group-id'] = 'Ошибка: отсутствует id группы.';
$string['e:no-tutor-necessary-data'] = 'Ошибка: отсутствует id преподавателя и/или id курса и/или квота.';
$string['e:student-already-chosen-theme'] = 'Ошибка: Студент {$a} уже выбрал(а) тематику своей курсовой работы.';
$string['e:tutor-total-quota-over'] = 'Ошибка: Квота преподавателя {$a->tutor} исчерпана. Студенту {$a->student} не назначена тематика курсовой работы.';
$string['e:student_not_enrolled'] = 'Ошибка: Вы не записаны в эту курсовую работу.';
$string['e:students_not_enrolled'] = 'Ошибка: В эту курсовую работу не записаны студенты.';
$string['e:tutors_not_enrolled'] = 'Ошибка: В эту курсовую работу не записаны руководители.';
$string['no_one_has_chosen_you_as_leader'] = 'На данный момент никто не выбрал(а) Вас в качестве руководителя.';

// Messages strings
$string['theme_selection_header'] = 'Студент выбрал Вас в качестве руководителя курсовой работы';
$string['student_graded_header'] = 'Изменена предварительная оценка или комментарий к курсовой работе';
$string['selection_removed_header'] = 'Выбор, сделаный Вами ранее в курсовой работе, был удалён вместе со всем достигнутым прогрессом. Выберете себе нового руководителя и продолжите работу с ним.';
$string['student_message'] = '<p>Пользователь {$a->tutor} {$a->date} в {$a->time} выбрал(а) Вас в качестве руководителя курсовой работы.</p>';
$string['tutor_message'] = '<p>Пользователь {$a->tutor} {$a->date} в {$a->time} предварительно оценил(а) и/или прокомментировал(а) Вашу курсовую работу.</p>';
$string['manager_message'] = '<p>Пользователь {$a->tutor} {$a->date} в {$a->time} отменил(а) Ваш выбор руководителя курсовой работы.</p><p>Весь полученный прогресс был удалён. Для продолжения работы выберите нового руководителя.</p>';
$string['answer_not_require'] = '<p>*Это сообщение отправлено автоматически и не требует ответа.</p>';
$string['grade_isnt_final'] = '<p>*Предварительная оценка не является окончательной.</p>';
$string['coursework_link1'] = 'Курсовая работа ';
$string['coursework_link2'] = 'доступна на сайте.';

