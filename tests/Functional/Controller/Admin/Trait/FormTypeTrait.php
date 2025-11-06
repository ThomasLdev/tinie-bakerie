<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Admin\Trait;

use App\Tests\Functional\Controller\Admin\Enum\FormButton;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

trait FormTypeTrait
{
    protected function getCreateForm(Crawler $crawler): Form
    {
        return $crawler->selectButton(FormButton::Create->value)->form();
    }

    protected function extractFormName(string $fullFormName): string
    {
        if (preg_match('/^([^\[]+)/', $fullFormName, $matches)) {
            return $matches[1];
        }

        throw new \BadFunctionCallException('Form name is invalid');
    }
}
