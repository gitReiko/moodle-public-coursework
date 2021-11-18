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

