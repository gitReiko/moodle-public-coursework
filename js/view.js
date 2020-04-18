const COURSE = 'course';
const STUDENT_FORM = 'student_form';

const THEME = 'theme';

const ru_confirm_theme_selection = 'Вы уверены, что хотите сделать именно такой выбор?\n(сделанный выбор невозможно самостоятельно отменить)';
const ru_confirm_remove_selection = 'Вы уверены, что хотите удалить выбор студента?\n\nВнимание:\n- данное действие нельзя будет отменить;\n- будет потерян весь прогресс студента.';
const ru_no_themes_error = 'Ошибка: нельзя сделать выбор, не выбрав тему.';
const ru_no_available_themes = 'Нет доступных тем';

function change_course_select()
{
    delete_previous_courses_select();

    var newCoursesSelect = get_new_courses_select();
    var allTeachers = document.getElementsByClassName('js_teacher');
    var newTeacher = document.getElementById('selected_teacher');

    for(var i = 0; i < allTeachers.length; i++)
    {
        if(allTeachers[i].dataset.teacherid == newTeacher.value)
        {
            var newCourseOption = get_new_course_option(allTeachers[i]);
            newCoursesSelect.appendChild(newCourseOption);
        }
    }

    var courseCell = document.getElementById('course_cell');
    courseCell.appendChild(newCoursesSelect);

    var courseOfNewTheme = get_course_of_new_theme(allTeachers, newTeacher.value);
    change_themes_select(courseOfNewTheme);
}

function delete_previous_courses_select()
{
    document.getElementById('selected_course').remove();
}

function get_new_courses_select()
{
    var newCoursesSelect = document.createElement('select');
    newCoursesSelect.name = COURSE;
    newCoursesSelect.id = 'selected_course';
    newCoursesSelect.setAttribute('form', STUDENT_FORM);
    newCoursesSelect.className = 'course';
    newCoursesSelect.style = 'width: 200px;'
    newCoursesSelect.onchange = function(event) { change_themes_select(this.value); }
    return newCoursesSelect;
}

function get_new_course_option(newTeacher)
{
    var newCourseOption = document.createElement('option');
    newCourseOption.value = newTeacher.dataset.courseid;
    newCourseOption.text = newTeacher.dataset.coursename;
    return newCourseOption;
}

function get_course_of_new_theme(allTeachers, newTeacher)
{
    var courseOfNewTheme;

    for(var i = 0; i < allTeachers.length; i++)
    {
        if(allTeachers[i].dataset.teacherid == newTeacher)
        {
            courseOfNewTheme = allTeachers[i].dataset.courseid;
            break;
        }
    }

    return courseOfNewTheme;
}

function change_themes_select(courseOfNewTheme)
{
    if(is_selected_another_course(courseOfNewTheme))
    {
        delete_previous_themes_select()

        var newThemeSelect = get_new_themes_select(courseOfNewTheme);
        var noAvailableThemes = true;
        var allThemes = document.getElementsByClassName('js_themes');

        for(var i = 0; i < allThemes.length; i++)
        {
            if(allThemes[i].dataset.course === courseOfNewTheme)
            {
                newThemeSelect.appendChild(get_available_theme_option(allThemes[i]));

                noAvailableThemes = false;
            }
        }

        if(noAvailableThemes) newThemeSelect.appendChild(get_no_available_themes_option());

        var ownThemeCheckboxLabel = document.getElementById('own_theme_checkbox_label');
        var themeCell = document.getElementById('theme_cell');
        themeCell.insertBefore(newThemeSelect, ownThemeCheckboxLabel);

        uncheckOwnThemeCheckbox();
    }
}

function is_selected_another_course(courseOfNewTheme)
{
    var courseOfPreviousTheme = document.getElementById('selected_theme').dataset.course;
    if(courseOfPreviousTheme !== courseOfNewTheme) return true;
    else return false;
}

function delete_previous_themes_select()
{

    document.getElementById('selected_theme').remove();
    document.getElementById('own_theme_input').disabled = true;
}

function get_new_themes_select(courseOfNewTheme)
{
    var themesSelect = document.createElement('select');
    themesSelect.name = THEME;
    themesSelect.id = 'selected_theme';
    themesSelect.setAttribute('form', STUDENT_FORM);
    themesSelect.dataset.course = courseOfNewTheme;
    return themesSelect;
}

function get_available_theme_option(theme)
{
    var availableOption = document.createElement('option');
    availableOption.value = theme.dataset.id;
    availableOption.text = theme.dataset.name;
    return availableOption;
}

function get_no_available_themes_option()
{
    var noThemesOption = document.createElement('option');
    noThemesOption.id = 'no_available_themes';
    noThemesOption.dataset.noavailablethemes = true;
    noThemesOption.text = ru_no_available_themes;
    return noThemesOption;
}

function uncheckOwnThemeCheckbox()
{
    document.getElementById('own_theme_checkbox').checked = false;
}

function process_own_theme_checkbox(event)
{
    var ownThemeInput = document.getElementById('own_theme_input');
    var availableThemesSelect = document.getElementById('selected_theme');

    if(event.checked)
    {
        ownThemeInput.disabled = false;
        availableThemesSelect.disabled = true;
    }
    else
    {
        ownThemeInput.disabled = true;
        availableThemesSelect.disabled = false;
    }
}

function process_student_coursework_choice()
{
    if(checkAvailabilityOfThemes() && confirm_teacher_selection()) return true;
    else return false;
}

function checkAvailabilityOfThemes()
{
    var ownThemeCheckbox = document.getElementById('own_theme_checkbox');

    if(ownThemeCheckbox.checked) return true;
    else
    {
        var noAvailableThemes = document.getElementById('no_available_themes');

        if(noAvailableThemes && noAvailableThemes.dataset.noavailablethemes)
        {
            alert(ru_no_themes_error);
            return false;
        }
    }

    return true;
}

function confirm_teacher_selection()
{
    var isConfirm = confirm(ru_confirm_theme_selection);

    if(isConfirm) return true;
    else return false;
}

function confirm_remove_selection()
{
    var isConfirm = confirm(ru_confirm_remove_selection);

    if(isConfirm) return true;
    else return false;
}

// --------------------------------------------------------
class SelectThemePage 
{
    static change_available_courses()
    {
        let availableCourses = this.get_leader_available_courses()
        this.update_course_select(availableCourses)
        let selectedCourse = document.getElementById('course_select').firstChild.value;
    }

    static get_leader_available_courses()
    {
        let selectedLeader = document.getElementById('leader_select').value;
        let leaders = document.getElementsByClassName('leaders_courses_js');

        let availableCourses;
        for(let i = 0; i < leaders.length; i++)
        {
            if(leaders[i].dataset.leader == selectedLeader)
            {
                availableCourses = leaders[i].dataset.courses.split(' ');
            }
        }

        return availableCourses;
    }

    static update_course_select(availableCourses)
    {
        // Delete select options
        let courseSelect = document.getElementById('course_select');
        courseSelect.innerHTML = '';

        // Insert neccessary options
        for(let course of availableCourses)
        {
            let option = document.createElement('option');
            option.value = course;
            option.innerHTML = this.get_course_name(course);

            courseSelect.append(option);
        }
    }

    static get_course_name(courseId)
    {
        let courses = document.getElementsByClassName('courses_js');
        for(let course of courses)
        {
            if(course.dataset.id == courseId)
            {
                return course.dataset.fullname;
            }
        }
    }


}











