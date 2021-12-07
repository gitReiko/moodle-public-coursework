
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

