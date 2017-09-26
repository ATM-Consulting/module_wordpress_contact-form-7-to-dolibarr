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


//use PHPUnit\Framework\TestCase;

require_once '../../lib/restclient.php';
require_once '../../lib/dolibarr_sync.php';


/**
* Class testWpCf7ToDolibarr
* @package test\unit
*/
class testWpCf7ToDolibarr extends WP_UnitTestCase
{
    private $options;
    
    /**
     * Global test setup
     */
    public static function setUpBeforeClass()
    {
        fwrite(STDOUT, __METHOD__ . "\n");
        
    }
    
    /**
     * Unit test setup
     */
    protected function setUp()
    {
        fwrite(STDOUT, __METHOD__ . "\n");
        $options = get_option( 'wpcf7_dolibarr_options' );
                
        // Replace tags
        $options['field_company'] = 'Company test';
        $options['field_firstname'] =  'firstname';
        $options['field_lastname'] = 'lastname';
        $options['field_email'] = 'johndoe@example.com';
        $options['subject'] = 'Email test';
        $options['message'] = 'This is a test message.';
        
        $this->options = $options;
        
        
        //$this->dolistoreMail = new \dolistoreMail();
        
        //$html = file_get_contents(dirname(__FILE__).'/../ex_info_produit.html');
        //$this->dolistoreMailExtract = new \dolistoreMailExtract($db, $html);
        
        //$this->dolistorextractActions = new \ActionsDolistorextract($db);
        
        //fwrite(STDOUT, __METHOD__ ." db->type=".$db->type." user->id=".$user->id."\n");
    }
    
    /**
     * Verify pre conditions
     */
    protected function assertPreConditions()
    {
        fwrite(STDOUT, __METHOD__ . "\n");
    }
    
    /**
     * Verify post conditions
     */
    protected function assertPostConditions()
    {
        fwrite(STDOUT, __METHOD__ . "\n");
    }
    
    /**
     * Unit test teardown
     */
    protected function tearDown()
    {
        fwrite(STDOUT, __METHOD__ . "\n");
    }
    
    
    /**
     * testCreateCustomerFromDatas
     *
     * @return	int
     *
     * The depends says test is run only if previous is ok
     */
    public function testSyncMessage()
    {
        
        $options = array();
        
        
        
        $dolibarrSync = new Wpcf7_dolibarr_sync($options);
        
        $searchCompany = $dolibarrSync->searchCompany($options['field_email']);
        
        // Company not found
        if ($searchCompany === 0) {
            // Create company
            $companyId = $dolibarrSync->saveCompany();
            
            // Save company into category
            $dolibarrSync->saveCompanyCategory($options['category_id']);
            
            // Save contact
            $contactId = $dolibarrSync->saveContact();
            
        }
        // Company found
        if ($searchCompany > 0) {
            $companyId = $searchCompany;
            $dolibarrSync->setCompanyId($companyId);
        }
        
        if($companyId > 0) {
            
            $dolibarrSync->saveMessage();
        }
        
        
        fwrite(STDOUT, __METHOD__." created socid=".$socid."\n");
        $this->assertGreaterThan(0, $companyId);
        
        return $socid;
    }
}