<?php
require_once($_SERVER['DOCUMENT_ROOT']. "/$ADMIN_DIR/articles/article_common.php");
require_once($_SERVER['DOCUMENT_ROOT']."/classes/Log.php");

// Check permissions
list($access, $User) = check_basic_access($_REQUEST);
if (!$access) {
	header("Location: /$ADMIN/logout.php");
	exit;
}


$Pub = Input::Get('Pub', 'int', 0);
$Issue = Input::Get('Issue', 'int', 0);
$Section = Input::Get('Section', 'int', 0);
$Language = Input::Get('Language', 'int', 0);
$Article = Input::Get('Article', 'int', 0);
//$sLanguage = Input::Get('sLanguage', 'int', 0);
$sLanguage = $Language;
$f_destination_publication_id = Input::Get('f_destination_publication_id', 'int', 0);
$f_destination_issue_id = Input::Get('f_destination_issue_id', 'int', 0);
$f_destination_section_id = Input::Get('f_destination_section_id', 'int', 0);
$f_article_name = Input::Get('f_article_name');
//$BackLink = Input::Get('Back', 'string', "/$ADMIN/articles/index.php", true);

if (!$User->hasPermission("AddArticle")) {
	camp_html_display_error(getGS("You do not have the right to add articles."), $BackLink);
	exit;
}

if (!Input::IsValid()) {
	camp_html_display_error(getGS("Invalid input: $1", Input::GetErrorString()), $BackLink);
	exit;	
}

$articleObj =& new Article($Pub, $Issue, $Section, $sLanguage, $Article);
$sectionObj =& new Section($Pub, $Issue, $Language, $Section);
$issueObj =& new Issue($Pub, $Language, $Issue);
$publicationObj =& new Publication($Pub);

$articleCopy = $articleObj->copy($f_destination_publication_id, $f_destination_issue_id, $f_destination_section_id, $User->getId());
$articleCopy->setTitle($f_article_name);

$logtext = getGS('Article $1 added to $2. $3 from $4. $5 of $6',
	$articleCopy->getName(), $sectionObj->getSectionId(),
	$sectionObj->getName(), $issueObj->getIssueId(),
	$issueObj->getName(), $publicationObj->getName() );
Log::Message($logtext, $User->getUserName(), 155);

$url = camp_html_article_url($articleCopy, $Language, "edit.php");
header("Location: $url");
exit;
?>