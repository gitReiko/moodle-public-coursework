
function toggle_student_checkbox(i)
{
    toggle_checkbox(i);
    change_row_color(i);
}

function toggle_checkbox(i)
{
    require(['jquery'], function($)
    {
        $('#checkbox-row-'+i).prop('checked', function(index, attr)
        {
            return attr == true ? false : true;
        });
    });
}

function change_row_color(i)
{
    require(['jquery'], function($)
    {
        $('#student-row-'+i).toggleClass('checked');
    });
}

