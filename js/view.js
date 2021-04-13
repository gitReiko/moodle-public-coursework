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
        this.update_course_select();
        this.update_themes_select();
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

    static update_course_select()
    {
        // Delete select options
        let courseSelect = document.getElementById('course_select');
        courseSelect.innerHTML = '';

        // Insert neccessary options
        let availableCourses = this.get_leader_available_courses();
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

    static update_themes_select()
    {
        // Delete select options
        let themeSelect = document.getElementById('theme_select');
        themeSelect.innerHTML = '';

        // Insert neccessary options
        let selectedCourse = document.getElementById('course_select').value;
        let themes = document.getElementsByClassName('themes_js');
        for(let theme of themes)
        {
            if(theme.dataset.courseId == selectedCourse)
            {
                let option = document.createElement('option');
                option.value = theme.dataset.themeId;
                option.innerHTML = theme.dataset.name;
    
                themeSelect.append(option);
            }
        }
    }

    static use_own_theme()
    {
        let useOwnTheme = document.getElementById('useOwnTheme');

        if(useOwnTheme.checked)
        {
            useOwnTheme.checked = false;
        }
        else 
        {
            useOwnTheme.checked = true;
        }

        this.offer_or_own_theme_switcher();
    }

    static offer_or_own_theme_switcher()
    {
        let ownTheme = document.getElementById('theme_select');
        let offerTheme = document.getElementById('own_theme_input');
        let useOwnTheme = document.getElementById('useOwnTheme');

        if(useOwnTheme.checked)
        {
            ownTheme.disabled = true;
            offerTheme.disabled = false;

        }
        else 
        {
            ownTheme.disabled = false;
            offerTheme.disabled = true;
        }
    }

}

function hide_or_show_block(id)
{
    block = document.getElementById(id);

    if(block.style.display == 'none')
    {
        block.style.display = 'block';
    }
    else 
    {
        block.style.display = 'none';
    }
}

class CustomTaskPage 
{
    static add_section()
    {
        let trId = document.getElementsByClassName('taskSections').length;

        let tr = document.createElement('tr');
        tr.id = 'section'+trId;
        tr.className = 'taskSections';
        let nameTd = this.create_name_td();
        tr.appendChild(nameTd);
        let dateTd = this.create_completion_date_td();
        tr.appendChild(dateTd);
        let btnsTd = this.create_action_buttons_td();
        tr.appendChild(btnsTd);


        let tbody = document.getElementById('sections_container');
        tbody.appendChild(tr);
    }

    static create_name_td()
    {
        let td = document.createElement('td');

        let input = document.createElement('input');
        input.type="text";
        input.name = 'name[]';
        input.minlength = 5;
        input.maxlength = 254;
        input.required = true;
        input.size = 80;
        input.autocomplete = 'off';
        input.setAttribute('form', 'custom_form');

        td.appendChild(input);
        return td;
    }

    static create_completion_date_td()
    {
        let td = document.createElement('td');

        let input = document.createElement('input');
        input.type="date";
        input.name = 'completion_date[]';
        input.autocomplete = 'off';
        input.setAttribute('form', 'custom_form');
        input.className = 'completion_date';

        td.appendChild(input);
        return td;
    }

    static create_action_buttons_td()
    {
        let td = document.createElement('td');

        let upButton = document.createElement('button');
        upButton.innerHTML = '↑';
        upButton.onclick = function()
        {
            CustomTaskPage.up_section(this);
        }
        td.appendChild(upButton);

        let downButton = document.createElement('button');
        downButton.innerHTML = '↓';
        downButton.onclick = function()
        {
            CustomTaskPage.down_section(this);
        }
        td.appendChild(downButton);

        let deleteButton = document.createElement('button');
        deleteButton.innerHTML = 'Удалить';
        deleteButton.onclick = function()
        {
            CustomTaskPage.delete_section(this);
        }

        td.appendChild(deleteButton);


        return td;
    }

    static delete_section(event)
    {
        let id = event.parentNode.parentNode.id;
        document.getElementById(id).remove();

        this.update_sections_ids();
    }

    static up_section(event)
    {
        let id = event.parentNode.parentNode.id;

        if(id != 'section0') 
        {
            let tbody = document.getElementById('sections_container');
            let tr = document.getElementById('section'+(parseInt(id.slice(-1))-1));
            let liftedTr = document.getElementById(id);
            tbody.insertBefore(liftedTr, tr);

            this.update_sections_ids();
        }
    }

    static down_section(event)
    {
        let id = event.parentNode.parentNode.id;
        let lastSectionId = document.getElementById('sections_container').lastChild.id;

        if(id != lastSectionId) 
        {
            let tbody = document.getElementById('sections_container');
            let liftedTr  = document.getElementById('section'+(parseInt(id.slice(-1))+1));
            let tr = document.getElementById(id);
            tbody.insertBefore(liftedTr, tr);

            this.update_sections_ids();
        }
    }

    static update_sections_ids()
    {
        let sections = document.getElementById('sections_container').childNodes;

        for(let i = 0; i < sections.length; i++)
        {
            sections[i].id = 'section'+i;
        }
    }

    static validate_form()
    {
        this.prepare_form();

        let sections = document.getElementById('sections_container').childNodes.length;

        if(sections) 
        {
            return true;
        }
        else
        {
            alert('Нельзя добавить задание без разделов.')
            return false;
        }
    }

    static prepare_form()
    {
        // delete old
        var obj=document.querySelectorAll('.sync_dates')
	    for(let i = 0; i < obj.length; i++) {
	        obj[i].remove();
	    }

        // insert new
        let form = document.getElementById('custom_form');
        let dates = document.getElementsByClassName('completion_date');
        for(let date of dates)
        { 
            let input = document.createElement('input');
            input.type="hidden";
            input.name = 'sync_dates[]';
            input.className = 'sync_dates';

            if(date.value) input.value = 1;
            else input.value = 0;

            form.appendChild(input);
        }
    }

}

// Scroll into the end of chat
require(['jquery'], function($)
{
    $(document).ready(function() 
    {
        var $container = $('.chat');
        $container[0].scrollTop = $container[0].scrollHeight;
    });    
});

function submit_form(id)
{
    document.getElementById(id).submit();
}

function open_close_table_row(tdId, ptrId)
{
    require(['jquery'], function($)
    {
        $('.'+tdId).toggleClass('hidden');

        $('#'+ptrId).toggleClass('fa-arrow-down');
        $('#'+ptrId).toggleClass('fa-arrow-up');
    });
}

function open_close_div(id)
{
    require(['jquery'], function($)
    {
        $('#'+id).toggleClass('hidden');
    });
}

