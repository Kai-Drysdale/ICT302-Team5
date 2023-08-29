<?php

function team5_plugin_before_footer()
{
	   \core\notification::add('a test message',  \core\output\notification::NOTIFY_SUCCESS);
}
