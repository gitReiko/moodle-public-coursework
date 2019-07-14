const CONFIG_MODULE = 'config_module';
const THEMES_MANAGEMENT = 'themes_management';

// Types of database events
const DB_EVENT = 'database_event';
const UPDATE = 'update';

// Type of database abstractions
const THEME = 'theme';
const NAME = 'name';
const ID = 'id';

const ru_warning_not_enough_quota = 'Внимание: Квота преподавателя меньше количества студентов в группе.';
const ru_some_student_will_not_be_assign = 'В случае распределения часть студентов не будет распределена.';

const ru_enter_new_theme_name = 'Введите новое название темы';

function change_bookmark(id)
{
    document.getElementById(id).submit();
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
