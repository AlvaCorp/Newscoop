<?php
/**
 * Campsite customized Smarty plugin
 * @package Campsite
 */

$g_documentRoot = $_SERVER['DOCUMENT_ROOT'];


/**
 * Campsite subscription_form block plugin
 *
 * Type:     block
 * Name:     subscription_form
 * Purpose:  Provides a...
 *
 * @param string
 *     $p_params
 * @param string
 *     $p_smarty
 * @param string
 *     $p_content
 *
 * @return
 *
 */
function smarty_block_subscription_form($p_params, $p_content, &$p_smarty, &$p_repeat)
{
    // gets the context variable
    $campsite = $p_smarty->get_template_vars('campsite');
    if (strtolower($p_params['type']) == 'by_publication') {
        $campsite->subs_by_type = 'publication';
    } elseif (strtolower($p_params['type']) == 'by_section') {
        $campsite->subs_by_type = 'section';
    }
    if (!isset($p_content)) {
        return null;
    }

    require_once $p_smarty->_get_plugin_filepath('shared','escape_special_chars');

    $html = '';

    if (!isset($p_params['type'])
    || (strtolower($p_params['type']) != 'by_section'
    && strtolower($p_params['type']) != 'by_publication')) {
        return null;
    }

    if (isset($p_params['template'])) {
        $template = new MetaTemplate($p_params['template']);
        if (!$template->defined()) {
            $template = null;
        }
    }
    $templateId = is_null($template) ? $campsite->template->identifier : $template->identifier;

    if (!isset($p_params['submit_button'])) {
        $p_params['submit_button'] = 'Submit';
    }

    $subsType = strtolower(CampRequest::GetVar('SubsType'));
    if ($subsType != 'trial' && $subsType != 'paid') {
        return null;
    }

    $publication = $campsite->publication;
    $timeUnits = $subsType == 'trial' ? $publication->subscription_trial_time : $publication->subscription_paid_time;
    $sectionsNumber = Section::GetNumUniqueSections($publication->identifier, false);

    $html .= "<form name=\"subscription_form\" action=\"\" method=\"post\">\n"
    ."<input type=\"hidden\" name=\"tpl\" value=\"$templateId\" />\n"
    ."<input type=\"hidden\" name=\"SubsType\" value=\"$subsType\" />\n"
    ."<input type=\"hidden\" name=\"tx_subs\" value=\"$timeUnits\" />\n"
    ."<input type=\"hidden\" name=\"nos\" value=\"$sectionsNumber\" />\n"
    ."<input type=\"hidden\" name=\"unitcost\" value=\""
    .$publication->subscription_unit_cost."\" />\n"
    ."<input type=\"hidden\" name=\"unitcostalllang\" value=\""
    .$publication->subscription_unit_cost_all_lang."\" />\n";

    $html .= $p_content;

    if ($subsType == 'paid' && isset($p_params['total']) != '') {
        $html .= $p_params['total']." <input type=\"text\" name=\"suma\" size=\"10\" "
        ."READONLY /> ".$currency;
    }

    $html .= "<input type=\"submit\" name=\"f_edit_subscription\" "
    ."id=\"subscriptionEdit\" value=\""
    .smarty_function_escape_special_chars($p_params['submit_button'])
    ."\" />\n";
    $html .= "</form>\n";

    if ($subsType == 'paid') {
        ?>
<script type="text/javascript">
/**
 * Returns true if the given object had the given property.
 */
function element_exists(object, property) {
	for (i in object) {
		if (object[i].name == property) {
			return true
		}
	}
	return false
}

/**
 * Used in subscription form; computes the subscription cost and updates
 * the corresponding field in the form.
 */
function update_subscription_payment() {
	var sum = 0
	var i
	var my_form = document.forms["subscription_form"]
	var subs_all_lang = false
	var unitcost = my_form.unitcost.value
	var lang_count = 1
	if (element_exists(my_form.elements, "subs_all_languages")
		&& my_form.subs_all_languages.checked) {
		unitcost = my_form.unitcostalllang.value
	} else if (element_exists(my_form.elements, "subscription_language[]")) {
		lang_count = 0
		for (i=0; i<my_form["subscription_language[]"].options.length; i++) {
			if (my_form["subscription_language[]"].options[i].selected) {
				lang_count++
			}
		}
	}
	for (i = 0; i < my_form.nos.value; i++) {
		if (element_exists(my_form.elements, "by")
			&& my_form.by.value == "publication") {
			sum = parseInt(sum) + parseInt(my_form["tx_subs"].value)
			continue
		}
		if (!my_form["cb_subs[]"][i].checked) {
			continue
		}
		var section = my_form["cb_subs[]"][i].value
		var time_var_name = "tx_subs" + section
		if (element_exists(my_form.elements, time_var_name)) {
			sum = parseInt(sum) + parseInt(my_form[time_var_name].value)
		} else if (element_exists(my_form.elements, "tx_subs")) {
			sum = parseInt(sum) + parseInt(my_form["tx_subs"].value)
		}
	}
	my_form.suma.value = Math.round(100 * sum * unitcost * lang_count) / 100
}
</script>
        <?php
}

?>
<script type="text/javascript">
function ToggleElementEnabled(id) {
	if (document.getElementById(id).disabled) {
		document.getElementById(id).disabled = false
	} else {
		document.getElementById(id).disabled = true
	}
}
</script>
<?php

return $html;
} // fn smarty_block_subscription_form

?>