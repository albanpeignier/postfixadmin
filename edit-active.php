<?php
/** 
 * Postfix Admin 
 * 
 * LICENSE 
 * This source file is subject to the GPL license that is bundled with  
 * this package in the file LICENSE.TXT. 
 * 
 * Further details on the project are available at : 
 *     http://www.postfixadmin.com or http://postfixadmin.sf.net 
 * 
 * @version $Id: edit-active.php 250 2007-12-02 13:59:33Z GingerDog $ 
 * @license GNU GPL v2 or later. 
 * 
 * File: edit-active.php 
 * Responsible for toggling the active status of a mailbox. 
 *
 * Template File: message.php
 *
 * Template Variables:
 *
 * tMessage
 *
 * Form POST \ GET Variables:
 *
 * fUsername
 * fDomain
 * fReturn
 */
require_once('common.php');

authentication_require_role('admin');
$SESSID_USERNAME = authentication_get_username();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   if (isset ($_GET['username'])) $fUsername = escape_string ($_GET['username']);
   if (isset ($_GET['alias'])) $fAlias = escape_string ($_GET['alias']); else $fAlias = escape_string ($_GET['username']);
   if (isset ($_GET['domain'])) $fDomain = escape_string ($_GET['domain']);
   if (isset ($_GET['return'])) $fReturn = escape_string ($_GET['return']);

   if (! (check_owner ($SESSID_USERNAME, $fDomain) || authentication_has_role('global-admin') ) )
   {
      $error = 1;
      $tMessage = $PALANG['pEdit_mailbox_domain_error'] . "<b>$fDomain</b>!</font>";
   }
   else
   {
      $setSql=('pgsql'==$CONF['database_type']) ? 'active=NOT active' : 'active=1-active';
      if ($fUsername != '')
      {
         $result = db_query ("UPDATE $table_mailbox SET $setSql WHERE username='$fUsername' AND domain='$fDomain'");
         if ($result['rows'] != 1)
         {
            $error = 1;
            $tMessage = $PALANG['pEdit_mailbox_result_error'];
         }
         else
         {
            db_log ($SESSID_USERNAME, $fDomain, 'edit_mailbox_state', $fUsername);
         }
      }
      if ($fAlias != '')
      {
         $result = db_query ("UPDATE $table_alias SET $setSql WHERE address='$fAlias' AND domain='$fDomain'");
         if ($result['rows'] != 1)
         {
            $error = 1;
            $tMessage = $PALANG['pEdit_mailbox_result_error'];
         }
         else
         {
            db_log ($SESSID_USERNAME, $fDomain, 'edit_alias_state', $fAlias);
         }
      }
   }

   if ($error != 1)
   {
      if ( preg_match( "/^list-virtual.php.*/", $fReturn ) || 
           preg_match( "/^search.php.*/", $fReturn )    )
      {
         //$fReturn appears OK, jump there
         header ("Location: $fReturn");
      }
      else
      {
         header ("Location: list-virtual.php?domain=$fDomain");
      }
      exit;
   }
}

include ("templates/header.php");
include ("templates/menu.php");
include ("templates/message.php");
include ("templates/footer.php");
/* vim: set expandtab softtabstop=3 tabstop=3 shiftwidth=3: */
?>
