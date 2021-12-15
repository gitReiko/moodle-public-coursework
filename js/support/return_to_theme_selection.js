
function confirm_theme_reselect(removeText)
{
    let isConfirm = confirm(removeText);

    if(isConfirm) return true;
    else return false;
}
