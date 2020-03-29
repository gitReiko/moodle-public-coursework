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

function change_bookmark(id)
{
    document.getElementById(id).submit();
}

// theme management functions
function edit_theme(themeId, themeName)
{
    let newName = prompt('Введите новое название темы', themeName);

    if((newName.length) < 5 || (newName.length > 254))
    {
        alert('Ошибка! Тема не изменена. Название темы должно быть не менее 4 символ и не более 254.');
        return false;
    }
    else 
    {
        document.getElementById('theme'+themeId).value = newName;
        return true;
    }
}

function check_teacher_quota_sufficiency(event)
{
    var selectValue = parseInt(event.value);

    if(selectValue !== 0)
    {
        for(var i = 0; i < event.childNodes.length; i++)
        {
            var optionValue = parseInt(event.childNodes[i].value);

            if(selectValue === optionValue)
            {
                var teacherQuota = parseInt(event.childNodes[i].dataset.teacherQuota);
                var countOfStudentsInGroup = parseInt(event.childNodes[i].dataset.countOfStudentsInGroup);

                if(teacherQuota < countOfStudentsInGroup)
                {
                    var message = ru_warning_not_enough_quota;
                    message += '( '+teacherQuota+' < '+countOfStudentsInGroup+' )\n';
                    message += ru_some_student_will_not_be_assign;

                    alert(message);
                }
            }
        }
    }
}

// StudentsDistributionOverview functions Start -->
function select_students_checkboxes(event)
{
    if(event.value == 'all')
    {
        var checkboxes = document.getElementsByClassName('students');
    }
    else
    {
        var checkboxes = document.getElementsByClassName(event.value);
    }

    for(var i = 0; i < checkboxes.length; i++)
    {
        checkboxes[i].checked = true;
    }
}

function unselect_students_checkboxes()
{
    var checkboxes = document.getElementsByClassName('students');

    for(var i = 0; i < checkboxes.length; i++)
    {
        checkboxes[i].checked = false;
    }
}

function validate_students_mass_action() 
{
    if(is_student_selected())
    {
        return true;
    }
    else
    {
        alert('Для выполнения действия нужно выбрать хотя бы одного студента.');
        return false;
    }
}

function is_student_selected()
{
    var checkboxes = document.getElementsByClassName('students');

    for(var i = 0; i < checkboxes.length; i++)
    {
        if(checkboxes[i].checked) return true;
    }

    return false;
}

// StudentsDistributionOverview functions End -->

// StudentsDistributionDistribute functions Start -->

function change_leader_courses()
{
    // !!! One function call leads to incorrect operation.
    remove_previous_leader_courses();
    remove_previous_leader_courses();

    var leaderSelect = document.getElementById('leaderselect');
    var leaderCourses = document.getElementsByClassName('jsleaders');

    var isFirstCourse = true;

    for(var i = 0; i < leaderCourses.length; i++)
    {
        if(leaderSelect.value == leaderCourses[i].dataset.leaderid)
        {
            add_course_to_courses_select(leaderCourses[i].dataset.coursename, leaderCourses[i].dataset.courseid);

            if(isFirstCourse)
            {
                display_or_hide_expand_quota_panel(leaderCourses[i].dataset.leaderid, leaderCourses[i].dataset.courseid);
                isFirstCourse = false;
            }
        }
    }
}

function remove_previous_leader_courses() 
{
    var options = document.getElementsByClassName('leadercourse');
    for(var i = 0; i < options.length; i++)
    {
        options[i].remove();
    }
}

function add_course_to_courses_select(coursename, coursevalue)
{
    var coursesSelect = document.getElementById('coursesselect');

    var courseOption = document.createElement('option');
    courseOption.className = 'leadercourse';
    courseOption.value = coursevalue;
    courseOption.text = coursename;

    coursesSelect.appendChild(courseOption);
}

function display_or_hide_expand_quota_panel_when_course_changes()
{
    var leaderSelect = document.getElementById('leaderselect');
    var courseSelect = document.getElementById('coursesselect');

    display_or_hide_expand_quota_panel(leaderSelect.value, courseSelect.value);
}

function display_or_hide_expand_quota_panel(leader, course)
{
    var expandQuotaPanel = document.getElementById('expandquotapanel');

    if(is_expand_panel_needed(leader, course))
    {
        expandQuotaPanel.style.display = 'block';
    }
    else
    {
        expandQuotaPanel.style.display = 'none';
    }
}

function is_expand_panel_needed(leader, course)
{
    var studentsCount = document.getElementById('studentscount').dataset.count;
    var leaderQuota = get_leader_quota(leader, course);

    if(studentsCount > leaderQuota) return true;
    else return false;
}

function get_leader_quota(leader, course)
{
    var leaderCourses = document.getElementsByClassName('jsleaders');
    for(var i = 0; i < leaderCourses.length; i++)
    {
        if(leaderCourses[i].dataset.leaderid == leader 
            && leaderCourses[i].dataset.courseid == course)
        {
            return leaderCourses[i].dataset.quota;
        }
    }
}

// StudentsDistributionDistribute functions End -->

// RemoveDistribution functions Start -->
function validate_students_removing()
{
    if(is_student_removal_selected())
    {
        if(confirm_students_removing()) return true;
    }

    return false;
}

function is_student_removal_selected()
{
    var checkboxes = document.getElementsByClassName('removeCheckbox');

    for(var i = 0; i < checkboxes.length; i++)
    {
        if(checkboxes[i].checked) return true;
    }

    return false;
}

function confirm_students_removing()
{
    var message = 'Вы уверены, что хотите удалить всю текущую деятельность выбранных студент? (восстановление удалённой информации невозможно)';
    var isConfirm = confirm(message);

    if(isConfirm) return true;
    else return false;
}

// RemoveDistribution functions End -->