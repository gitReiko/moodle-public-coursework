
function toggle_student_checkbox(i)
{
    change_row_color(i);
    toggle_checkbox(i);
    show_hide_delete_button();
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

function show_hide_delete_button()
{
    let checkboxes = document.getElementsByClassName('delete_checkboxes');
    let button = document.getElementById('delete_button');

    button.setAttribute('disabled', 'disabled');

    for(let checkbox of checkboxes)
    {
        if(checkbox.checked)
        {
            button.removeAttribute('disabled');
            return;
        }
    }
}

