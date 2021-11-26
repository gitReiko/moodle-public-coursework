
require(['jquery'], function($)
{
    $(document).ready(function() {
        document.getElementById('delete_button').setAttribute('disabled', 'disabled');
    });
});

function toggle_student_checkbox(i)
{
    change_row_color(i);
    toggle_checkbox(i);
    show_hide_delete_button();
}

function toggle_checkbox(i)
{
    let checkbox = document.getElementById('checkbox-row-'+i);

    if(checkbox.checked)
    {
        checkbox.removeAttribute('checked');
    }
    else 
    {
        checkbox.setAttribute('checked', 'checked');
    }
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

function confirm_students_removing(removeText)
{
    let isConfirm = confirm(removeText);

    if(isConfirm) return true;
    else return false;
}
