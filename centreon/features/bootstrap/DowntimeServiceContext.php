<?php
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Tester\Exception\PendingException;
use Centreon\Test\Behat\CentreonContext;
use Centreon\Test\Behat\ConfigurationPollersPage;
use Centreon\Test\Behat\MetaServiceConfigurationPage;
use Centreon\Test\Behat\MonitoringServicesPage;
use Centreon\Test\Behat\ServiceMonitoringDetailsPage;


/**
 * Defines application features from the specific context.
 */
class DowntimeServiceContext extends CentreonContext
{

    public function __construct()
    {
        parent::__construct();
        $this->metaName = 'testmeta';
    }

    /**
     * @Given I have a meta service
     */
    public function iHaveAMetaServices()
    {
        $metaservicePage = new MetaServiceConfigurationPage($this);
        $metaservicePage->setProperties(array(
            'name' => $this->metaName,
            'check_period' => 5,
            'max_check_attempts' => 1,
            'normal_check_interval' => 1,
            'retry_check_interval' => 1));
        $metaservicePage->save();
        (new ConfigurationPollersPage($this))->restartEngine();
    }

    /**
     * @When I place a downtime
     */
    public function iPlaceADowntime()
    {
        $this->visit('main.php?p=20201&o=svcd&host_name=_Module_Meta&service_description=meta_1');
        $this->assertFind('css', '.ListTable.table.linkList tr.list_two:nth-child(4) a')->click();
        sleep(1);
        $this->assertFind('css', 'textarea[name="comment"]')->setValue('downtime');
        $this->assertFind('css', 'input[name="submitA"]')->click();
    }

    /**
     * @When I place a comment
     */
    public function iPlaceAComment()
    {
        $this->visit('main.php?p=20201&o=svcd&host_name=_Module_Meta&service_description=meta_1');
        $this->assertFind('css', '.ListTable.table.linkList tr.list_one:nth-child(5) a')->click();
        sleep(1);
        $this->assertFind('css', 'textarea[name="comment"]')->setValue('downtime');
        $this->assertFind('css', 'input[name="submitA"]')->click();
        sleep(3);

    }


    /**
     * @Then this one appears in the interface
     */
    public function thisOneAppearsInTheInterface ()
    {
        $this->visit('main.php?p=21002');
        $this->getSession()->getPage()->has('css', 'table.ListTable tbody tr.list_two td.ListColLeft a');
    }


    /**
     * @Then this one appears in the interface in downtime
     */
    public function thisOneAppearsInTheInterfaceInDowntime ()
    {
        $this->spin(function ($context) {
            $page = new ServiceMonitoringDetailsPage(
                $context,
                '_Module_Meta',
                'meta_1'
            );
            $props = $page->getProperties();
            return $props['in_downtime'];
        },
            3);
    }





}
