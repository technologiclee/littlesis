<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class FedspendingFiling extends BaseFedspendingFiling
{
  public function getSourceUrl()
  {
    if (!$this->fedspending_id)
    {
      return null;
    }

    return FedSpendingScraper::$baseFilingUrl . $this->fedspending_id;
  }
}