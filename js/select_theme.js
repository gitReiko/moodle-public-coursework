
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
        let leaders = document.getElementsByClassName('teachers_and_their_courses_js_data');

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
        let courses = document.getElementsByClassName('courses_js_data');
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
        let selectedCourse = document.getElementById('course_select').value;
        let themes = document.getElementsByClassName('themes_js_data');

        if(this.is_course_themes_not_exists(selectedCourse, themes))
        {
            this.disable_proposed_themes();
        }
        else 
        {
            this.update_proposed_themes(selectedCourse, themes);
        }
    }

    static is_course_themes_not_exists(selectedCourse, themes)
    {
        for(let theme of themes)
        {
            if(theme.dataset.courseId == selectedCourse)
            {
                return false;
            }
        }

        return true;
    }

    static disable_proposed_themes()
    {
        this.disable_proposed_themes_select();
        this.delete_select_options();
        this.add_missing_themes_option();

        this.disable_use_own_theme();
        this.enable_own_theme_input();
    }

    static update_proposed_themes(selectedCourse, themes)
    {
        this.enable_proposed_themes_select();
        this.delete_select_options();
        this.add_proposed_themes_options(selectedCourse, themes);

        this.enable_use_own_theme();
        this.disable_own_theme_input();
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

    static enable_proposed_themes_select()
    {
        document.getElementById('theme_select').disabled = false;
    }

    static disable_proposed_themes_select()
    {
        document.getElementById('theme_select').disabled = true;
    }

    static delete_select_options()
    {
        document.getElementById('theme_select').innerHTML = '';
    }

    static add_missing_themes_option()
    {
        let option = document.createElement('option');
        option.value = 0;
        option.innerHTML = 'Отсутствуют';
        document.getElementById('theme_select').append(option);
    }

    static add_proposed_themes_options(selectedCourse, themes)
    {
        let themeSelect = document.getElementById('theme_select');

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

    static enable_use_own_theme()
    {
        document.getElementById('useOwnThemeParagraph').onclick = function()
        {
            SelectThemePage.use_own_theme();
        }

        document.getElementById('useOwnTheme').onclick = function()
        {
            SelectThemePage.use_own_theme();
            SelectThemePage.offer_or_own_theme_switcher();
        }
        document.getElementById('useOwnTheme').disabled = false;
        document.getElementById('useOwnTheme').checked = false;
    }

    static disable_use_own_theme()
    {
        document.getElementById('useOwnThemeParagraph').onclick = '';
        document.getElementById('useOwnTheme').disabled = true;
        document.getElementById('useOwnTheme').onclick = '';
    }

    static enable_own_theme_input()
    {
        document.getElementById('own_theme_input').disabled = false;
    }

    static disable_own_theme_input()
    {
        document.getElementById('own_theme_input').disabled = true;
    }


}
