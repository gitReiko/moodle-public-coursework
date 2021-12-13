
function toggle_themes_list(id)
{
    require(['jquery'], function($)
    {
        $('#'+id).toggleClass('themes_list');
    });
}

