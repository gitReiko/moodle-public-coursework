<?php 

namespace Coursework\Lib;

class Enums 
{

    const ID = 'id';

    // Group modes
    const NO_GROUPS = 0;
    const ISOLATED_GROUPS = 1;
    const VISIBLE_GROUPS = 2;

    // States
    const COURSEWORK = 'coursework';
    const SECTION = 'section';
    const THEME_SELECTION = 'theme_selection';
    const TASK_RECEIPT = 'task_receipt';
    const STARTED = 'started';
    const RETURNED_FOR_REWORK = 'returned_for_rework';
    const SENT_FOR_CHECK = 'sent_for_check';
    const READY = 'ready';

    // outdated statuses !!!
    const NEED_TO_FIX = 'need_to_fix';
    const NOT_READY = 'not_ready';
    const SENT_TO_CHECK = 'sent_to_check';

    
}

