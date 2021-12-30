
function confirm_leader_deleting(confText)
{
    let isConfirm = confirm(confText);

    if(isConfirm) return true;
    else return false;
}
