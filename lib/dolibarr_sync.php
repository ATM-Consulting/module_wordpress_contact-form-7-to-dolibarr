<?php

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Class to save Wordpress Contact Form7 datas through Dolibarr API
 * @author jfefe
 *
 */
class Wpcf7_dolibarr_sync
{

	public $datas;		  // all datas from form in a single array
	public $company;		// array to store company information
	public $contact;		// array to store contact information
	public $message;		// Message from contact form
	public $subject;		// Message subject
	public $api;			// RestClient Object
	public $actionCode;	 // Action code for event
	public $ownerId;		// User owner ID

	public function __construct($datas) {
		$this->datas = $datas;

		$this->api = new RestClient(array(
			'base_url' => $datas['api_url'],
			'format' => "json",
			'user_agent' => "WPCF7-dolibarr",
			'headers' => ['DOLAPIKEY' => $datas['api_key']]
		));

		$this->setCompany($datas['field_company'], $datas['field_email'], $datas['field_siren']);
		$this->setContact($datas['field_lastname'], $datas['field_firstname'], $datas['field_email'], $datas['field_phone']);
		$this->setMessage($datas['message']);
		$this->setMessageSubject($datas['subject']);

		$this->actionCode = $datas['action_code'];
		$this->ownerId = $datas['userownerid'];
	}

	/**
	 * @param string $name
	 * @param string $email
	 * @throws RestClientException
	 */
	private function setCompany($name, $email, $siren) {
		$this->company['name'] = $name;
		$this->company['email'] = $email;
        $this->company['siren'] = $siren;
		$this->company['provenance'] = 'INT';

		// Retreive FR country : Only work with DOL_VERSION >= 7.0
		$result = $this->api->get('setup/dictionary/countries', array('sqlfilters' => "code = 'FR'"));
		if ($result->info->http_code == 200) {	// We found
			$resArray = $result->decode_response();
			$firstKey = key($resArray);
			$country = $resArray[$firstKey];
		}
		else if($result->info->http_code == 501) {
			// Only work with DOL_VERSION <= 6.0
			$result = $this->api->get('dictionarycountries', array('sqlfilters' => "code = 'FR'"));
			if($result->info->http_code == 200) {
				$resArray = $result->decode_response();
				$firstKey = key($resArray);
				$country = $resArray[$firstKey];
			}
		}
		$this->company['country_id'] = empty($country->id) ? 0 : $country->id;
	}

	public function setCompanyId($id) {
		$this->company['id'] = $id;
	}

	public function setContactId($id) {
		$this->contact['id'] = $id;
	}

	/**
	 * @param string $lastname
	 * @param string $firstname
	 * @param string $email
	 * @param string $phone
	 */
	private function setContact($lastname, $firstname, $email, $phone) {
		$this->contact['lastname'] = $lastname;
		$this->contact['firstname'] = $firstname;
		$this->contact['email'] = $email;
		$this->contact['phone'] = $phone;
		$this->contact['socid'] = $this->company['id'];
	}

	private function setMessageSubject($subject) {
		$this->subject = $subject;
	}

	private function setMessage($message) {
		$this->message = $message;
	}

	/**
	 * Search if company exists with email
	 * 
	 * @param string $email
	 * @return int 
	 */
	public function searchCompany($email) {
		$return = -1;
		$result = $this->api->get("thirdparties", ['sqlfilters' => "(t.email:LIKE:'".$email."')"]);
		// Au moins un résultat trouvé
		if ($result->info->http_code == 200) {
			$resArray = $result->decode_response();
			$return = $resArray[0]->id;
		}

		// Aucun résultat trouvé
		if ($result->info->http_code === 404) {
			$return = 0;
		}

		// Error
		return $return;
	}

	public function searchContact($email) {
		
	}

	/**
	 * Save company with Dolibarr API
	 * @return int Company ID or 0 if not created
	 */
	public function saveCompany() {
		// Informations about company to create
		$datas = array(
			'name' => $this->company['name'],
			'email' => $this->company['email'],
            'idprof1' => $this->company['siren'],
			'country_id' => !empty($this->company['siren']) ? 1 : $this->company['country_id'], // Force country to France if SIREN is set
			'array_options' => array('options_provenance' => $this->company['provenance']),
            'client'    => 2, // Set as Prospect,
            'code_client' => 'auto'
		);
		$result = $this->api->post("thirdparties", $datas);

		// Request ok
		if ($result->info->http_code == 200) {
			$this->setCompanyId((int) $result->response);

			return (int) $result->response;
		}
		return 0;
	}

	public function saveCompanyCategory($idCategory) {
		$datas = array(
			'id' => $this->company['id'],
			'category_id' => $idCategory
		);
		$result = $this->api->post("thirdparties/".$this->company['id']."/addCategory", $datas);

		// Request ok
		if ($result->info->http_code == 200) {
			return (int) $result->response;
		}
		return 0;
	}

	/**
	 * Save contact with Dolibarr API
	 * @return int Contact ID or 0 if not created
	 */
	public function saveContact() {
		// Information about contact to create
		$datas = array(
			'lastname' => $this->contact['lastname'],
			'firstname' => $this->contact['firstname'],
			'email' => $this->contact['email'],
			'phone_pro' => $this->contact['phone'],
			'socid' => $this->company['id']
		);
		$result = $this->api->post("contacts", $datas);

		// Request ok
		if ($result->info->http_code == 200) {
			$this->setContactId((int) $result->response);
			return (int) $result->response;
		}
		return 0;
	}

	/**
	 * Create an event with message into Dolibarr API
	 */
	public function saveMessage() {
		$datas = array(
			'label' => $this->subject,
			'type_code' => $this->actionCode,
			'datep' => time(),
			'durationp' => '-1',
			'punctual' => 1,
			'fulldayevent' => '0',
			'userownerid' => $this->ownerId,
			'socid' => $this->company['id'],
			'contactid' => $this->contact['id'],
			'note' => $this->message,
            'percentage' => 100
		);
		$result = $this->api->post("agendaevents", $datas);

		// Request ok
		if ($result->info->http_code == 200) {
			return (int) $result->response;
		}

		return 0;
	}

	public function syncContactForm() {
		// Check
		if (is_array($this->company) && !count($this->company)) {
			$error++;
		}
	}

}
