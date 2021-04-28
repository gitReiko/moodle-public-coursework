
class Chat 
{
    static remove_title_text()
    {
        let input = document.getElementById('chatMessageInput');

        if(input.value == input.title)
        {
            input.value = '';
        }
    }

    static send_chat_message()
    {
        let form = document.getElementById('messageFormId');
        let input = document.getElementById('chatMessageInput');

        if(input.value.trim() == '')
        {
            return;
        }
        else if(input.value == input.title)
        {
            return;
        }
        else 
        {
            form.submit();
        }
    }


}
