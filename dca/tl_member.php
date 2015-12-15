<?php
$GLOBALS['TL_DCA']['tl_member']['fields']['username']['load_callback'][] = array('fh_tl_member', 'setDefaultUserName');
$GLOBALS['TL_DCA']['tl_member']['fields']['username']['save_callback'][] = array('fh_tl_member', 'saveDefaultUserName');

$GLOBALS['TL_DCA']['tl_member']["subpalettes"]['login'] = 'username, generatePassword, password, javascriptField';

$GLOBALS['TL_DCA']['tl_member']['fields']['generatePassword'] =array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_member']['generatePassword'],
    'inputType'               => 'text',
    'input_field_callback'	=> array('fh_tl_member','generatePassword'),
);

$GLOBALS['TL_DCA']['tl_member']['fields']['javascriptField'] = array 
(
	'input_field_callback' => array('fh_tl_member', 'setDefaultUserNameJavaScript')
);

class fh_tl_member extends Backend{


	public function setDefaultUserNameJavaScript($dc, $xlabel) {

		if ($dc->activeRecord->firstname == "" && $dc->activeRecord->lastname == "") {
			$script = '
				<script type="text/javascript">
					var firstname = document.getElementById("ctrl_firstname").value;
					var lastname = document.getElementById("ctrl_lastname").value;
					var username = firstname.toLowerCase().substring(0,1) + lastname.toLowerCase();
					username = username.replace(/ä/g, "ae").replace(/ö/g, "oe").replace(/ü/g, "ue").replace(/ß/g, "ss");
					document.getElementById("ctrl_username").value = username;
				</script>';

				return $script;
		}
		return;
	}

	public function setDefaultUserName($varValue, DataContainer $dc) {
		if ($dc->activeRecord->firstname == "" && $dc->activeRecord->lastname == "") {
			return;
		}

		if ($varValue == '' || !isset($varValue)) {
			$varValue = substr(mb_strtolower($dc->activeRecord->firstname), 0, 1).mb_strtolower(iconv("UTF-8", "ASCII//TRANSLIT", $dc->activeRecord->lastname));
		}
		return $varValue;
	}

	public function saveDefaultUserName($varValue, DataContainer $dc) {
		$member = \MemberModel::findById($dc->id);
		$member->username = $varValue;
		$member->save();
		return $varValue;
	}

	public function generatePassword($dc, $xlabel) {
		return '<button style="margin-top:14px;" id="btn-pw-generate" class="tl_submit" type="button">Password generieren</button>
                <script type="text/javascript">
                    function chr(codePt) {
                        if (codePt > 0xFFFF) {
                        codePt -= 0x10000;
                        return String.fromCharCode(0xD800 + (codePt >> 10), 0xDC00 + (codePt & 0x3FF));
                      }
                      return String.fromCharCode(codePt);
                    }

					function changeInputType(oldObject, oType) {
					    var newObject = document.createElement("input");
					    newObject.type = oType;
					    if(oldObject.size) newObject.size = oldObject.size;
					    if(oldObject.value) newObject.value = oldObject.value;
					    if(oldObject.name) newObject.name = oldObject.name;
					    if(oldObject.id) newObject.id = oldObject.id;
					    if(oldObject.className) newObject.className = oldObject.className;
					    oldObject.parentNode.replaceChild(newObject,oldObject);
					    return newObject;
					}					

                    function getPassword() {
                        var characters = [];
                        var i = 65;

                        while (i <= 122) {
                           characters.push(chr(i));
                            i++;
                            if (i == 91) {
                                i = 97;
                            }
                        }
                        i = 0;
                        while (i <= 9) {
                            characters.push(i);
                            i++;
                        }
                        
                        var password = "";
                        for(var i = 0; i < 8; i++) {
                            var number = Math.floor((Math.random() * characters.length));
                            password += characters[number];
                        }

                        return password;
                    }

                    document.getElementById("btn-pw-generate").addEventListener("click", function(e) {
						if (document.getElementById("ctrl_password").type = "password") {
	                		changeInputType(document.getElementById("ctrl_password"), "text");
						}
						if (document.getElementById("ctrl_password_confirm").type = "password") {
	                		changeInputType(document.getElementById("ctrl_password_confirm"), "text");
						}
						var password = getPassword();
                    	document.getElementById("ctrl_password").value = password;
                    	document.getElementById("ctrl_password_confirm").value = password;
                	});
                   
                </script>';
	}

}