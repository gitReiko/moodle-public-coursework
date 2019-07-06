const GROUPS = 'groups';
const TEACHERS = 'teachers';
const COURSES = 'courses';
const QUOTAS = 'quotas';
const TEACHER = 'teacher';
const CONFIG_MODULE = 'config_module';

// Coursework modules
const LEADERS_SETTING = 'participants_management';
const THEMES_MANAGEMENT = 'themes_management';

// Types of database events
const DB_EVENT = 'database_event';
const ADD = 'add';
const DEL = 'delete';
const UPDATE = 'update';

// Type of database abstractions
const THEME = 'theme';
const NAME = 'name';
const COURSE = 'course';
const ID = 'id';

const ru_delete = 'Удалить';
const ru_spend_quota = 'Распределите оставшиеся квоты';
const ru_confirm_tutor_delete = 'Необратимо удалить преподавателя и результаты его работы из курсовой работы?';
const ru_confirm_group_delete = 'Необратимо удалить группы и результаты их работы из курсовой работы?';
const ru_warning_not_enough_quota = 'Внимание: Квота преподавателя меньше количества студентов в группе.';
const ru_some_student_will_not_be_assign = 'В случае распределения часть студентов не будет распределена.';

const ru_enter_new_theme_name = 'Введите новое название темы';

function change_bookmark(id)
{
    document.getElementById(id).submit();
}

function add_tutor()
{
    var table = document.getElementById("tutors_table");
    var row_count = Number(table.dataset.rows);

    var row = document.createElement("tr");
    row.dataset.index = row_count;
    table.appendChild(row);

    row.appendChild(tutor_cell());
    row.appendChild(course_cell());
    row.appendChild(quota_cell());
    row.appendChild(delete_cell(row_count));

    table.dataset.rows = Number(table.dataset.rows) + 1;
}

function tutor_cell()
{
    var tutors = document.getElementsByClassName('tutors');

    var select = document.createElement('select');
    select.name = TEACHERS+'[]';
    select.style = 'width: 250px;';

    select.autocomplete = "off";
    select.required = true;

    for(var i = 0; i < tutors.length; i++)
    {
        var option = document.createElement('option');
        option.value = tutors[i].dataset.id;
        option.text = tutors[i].dataset.name;
        select.appendChild(option);
    }

    var td = document.createElement('td');
    td.appendChild(select);
    return td;
}

function course_cell()
{
    var courses = document.getElementsByClassName('courses');

    var select = document.createElement('select');
    select.name = COURSES+'[]';
    select.style = 'width: 250px;';

    select.autocomplete = "off";
    select.required = true;

    for(var i = 0; i < courses.length; i++)
    {
        var option = document.createElement('option');
        option.value = courses[i].dataset.id;
        option.text = courses[i].dataset.name;
        select.appendChild(option);
    }

    var td = document.createElement('td');
    td.appendChild(select);
    return td;
}

function quota_cell()
{
    var input = document.createElement('input');
    input.className = 'quotas';
    input.type = 'number';
    input.name = QUOTAS+'[]';
    input.style = 'width: 50px;';
    input.onchange = function()
    {
        count_members();
    }

    input.autocomplete = "off";
    input.required = true;
    input.min = 1;

    var td = document.createElement('td');
    td.appendChild(input);
    return td;
}

function delete_cell(row_count)
{
    var button = document.createElement('button');
    button.innerHTML = ru_delete;
    button.onclick = function()
    {
        var rows = document.getElementById('tutors_table').childNodes;

        for(var i = 0; i < rows.length; i++)
        {
            if(rows[i].dataset.index == row_count) rows[i].remove();
        }

        count_members();
    }

    var td = document.createElement('td');
    td.appendChild(button);
    return td;
}

function count_members()
{
    var membersCount = 0;

    // Count members of selected groups
    var group = document.getElementsByClassName('group');
    for(var i = 0; i < group.length; i++)
    {
        if(group[i].selected)
        {
            membersCount += Number(group[i].dataset.count);
        }
    }

    // Subtract spent quota
    var quotas = document.getElementsByClassName('quotas');
    for(var i = 0; i < quotas.length; i++)
    {
        membersCount -= quotas[i].value;
    }

    document.getElementById('quota_left').innerHTML = membersCount;
}

function submit_form()
{
    if(is_delete_group())
    {
        if(document.getElementById('quota_left').innerHTML == 0)
        {
            return true;
        }
        else
        {
            alert(ru_spend_quota);
            return false;
        }
    }

    return false;
}

function delete_tutor(rowID)
{
    var isDelete = confirm(ru_confirm_tutor_delete);

    if(isDelete)
    {
        if(is_delete_group())
        {
            var form = document.getElementById('enroll_form');

            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = DEL+TEACHER;
            input.value = rowID;

            form.appendChild(input);
            return true;
        }
    }

    return false;
}

function is_delete_group()
{
    var selectedGroups = document.getElementsByClassName('group');

    for(var i = 0; i < selectedGroups.length; i++)
    {
        if(selectedGroups[i].dataset.initial == 'true' && (!selectedGroups[i].selected))
        {
            var isConfirm = confirm(ru_confirm_group_delete);

            if(isConfirm) return true;
            else return false;
        }
    }

    return true;
}

// theme management functions
function edit_theme(cmid, id, name)
{
    var newName = prompt(ru_enter_new_theme_name, name);

    // Create form and submit it
    var form = document.createElement('form');
    form.method = 'form';

    var cmidinput = document.createElement('input');
    cmidinput.type = 'hidden';
    cmidinput.name = ID;
    cmidinput.value = cmid;
    form.appendChild(cmidinput);

    var mod = document.createElement('input');
    mod.type = 'hidden';
    mod.name = CONFIG_MODULE;
    mod.value = THEMES_MANAGEMENT;
    form.appendChild(mod);

    var db = document.createElement('input');
    db.type = 'hidden';
    db.name = DB_EVENT;
    db.value = UPDATE+THEME;
    form.appendChild(db);

    var themeid = document.createElement('input');
    themeid.type = 'hidden';
    themeid.name = THEME+ID;
    themeid.value = id;
    form.appendChild(themeid);

    var name = document.createElement('input');
    name.type = 'hidden';
    name.name = THEME+NAME;
    name.value = newName;
    form.appendChild(name);

    document.body.appendChild(form);
    form.submit();
}

function check_tutor_quota_sufficiency(event)
{
    var selectValue = parseInt(event.value);

    if(selectValue !== 0)
    {
        for(var i = 0; i < event.childNodes.length; i++)
        {
            var optionValue = parseInt(event.childNodes[i].value);

            if(selectValue === optionValue)
            {
                var tutorQuota = parseInt(event.childNodes[i].dataset.tutorQuota);
                var countOfStudentsInGroup = parseInt(event.childNodes[i].dataset.countOfStudentsInGroup);

                if(tutorQuota < countOfStudentsInGroup)
                {
                    var message = ru_warning_not_enough_quota;
                    message += '( '+tutorQuota+' < '+countOfStudentsInGroup+' )\n';
                    message += ru_some_student_will_not_be_assign;

                    alert(message);
                }
            }
        }
    }
}
