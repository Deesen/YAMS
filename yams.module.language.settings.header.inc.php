<?php
/**
 * Manages the Language Settings header of the YAMS module interface
 *
 * @author PMS (http://modxcms.com/forums/index.php?action=profile;u=12570)
 * @copyright Nashi Power (http://nashi.podzone.org/) 2009
 * @license GPL v3
 * @package YAMS (http://modxcms.com/extras/package/?package=543)
 * @see Forum (http://modxcms.com/forums/index.php/board,381.0.html)
 *
 */
 
  if ( ! isset( $modx ) )
  {
    exit();
  }
  switch ( $mode )
  {
  case 'edit_multi':
    ?><tr>
                    <th class="gridHeader" rowspan="2">Help</th>
                    <th class="gridHeader" rowspan="2">Action/Name</th><?php
    foreach ( $allLangIds as $langId )
    {
      ?><th class="gridHeader"><?php
      if ( $langId == $edit_lang )
      {
        ?><button name="yams_action" type="submit" value="submit_multi,<?php echo $langId; ?>">Submit</button><?php
      }
      ?></th><?php
    }
  ?>
  </tr>
  <tr>
    <?php
    foreach ( $allLangIds as $langId )
    {
      ?><th class="gridHeader"><?php
      if ( $langId == $edit_lang )
      {
        ?><button name="yams_action" type="submit" value="cancel,<?php echo $langId; ?>">Cancel</button><?php
      }
  ?></th><?php
    }
    ?>
  </tr><?php
    break;
  case 'add':
  default:
    ?><tr>
    <th class="gridHeader" rowspan="3">Help</th>
    <th class="gridHeader" rowspan="3">Action/Name</th><?php
    foreach ( $allLangIds as $langId )
    {
      ?><th class="gridHeader">
      <button name="yams_action" type="submit" value="edit_multi,<?php echo $langId; ?>">Edit</button>
    </th><?php
      }
      ?><th class="gridHeader"><button name="yams_action" type="submit" value="add">Add</button></th>
  </tr>
  <tr><?php
  foreach ( $activeLangIds as $langId )
  {
    ?><th class="gridHeader"><?php
    if ( $langId == $defaultLangId )
    {
      // <!--<button name="yams_action" type="submit" disabled="disabled" value="deactivate,<php echo $langId; >">Deactivate</button>-->
    }
    else
    {
      ?><button name="yams_action" type="submit" value="deactivate,<?php echo $langId; ?>">Deactivate</button><?php
    }
    ?></th><?php
  }
  foreach ( $inactiveLangIds as $langId )
  {
    ?><th class="gridHeader"><?php
    if ( $langId == $defaultLangId )
    {
      // <!--<button name="yams_action" type="submit" disabled="disabled" value="deactivate,<php echo $langId; >">Deactivate</button>-->
    }
    else
    {
      ?><button name="yams_action" type="submit" value="activate,<?php echo $langId; ?>">Activate</button><?php
    }
    ?></th><?php
  }
  ?><th class="gridHeader"></th>
  </tr>
  <tr><?php
      foreach ( $activeLangIds as $langId )
      {
        ?><th class="gridHeader"><?php
        if ( $langId == $defaultLangId )
        {
          ?>Is Default<?php
        }
        else
        {
        ?><button name="yams_action" type="submit" value="default,<?php echo $langId; ?>">Set Default</button><?php
        }
        ?></th><?php
      }
      foreach ( $inactiveLangIds as $langId )
      {
        ?><th class="gridHeader"><button name="yams_action" type="submit" value="delete,<?php echo $langId; ?>">Delete</button></th><?php
      }
      ?><th class="gridHeader"></th>
    </tr><?php
        }
?>