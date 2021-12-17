
function edit_theme(themeId, themeName, enterText, errorText)
{
    let newName = prompt(enterText, themeName);

    if((newName.length) < 5 || (newName.length > 254))
    {
        alert(errorText);
        return false;
    }
    else 
    {
        document.getElementById('theme'+themeId).value = newName;
        return true;
    }
}

