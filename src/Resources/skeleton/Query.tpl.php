<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use ApiClientBundle\Model\<?= $errorResponseClassDetails ? 'AbstractQuery' : 'AbstractQueryWithGenericErrorResponse'?>;
use ApiClientBundle\Interfaces\ClientConfigurationInterface;
use <?= $clientFullName ?>;
use <?= $responseClassNameDetails->getFullName() ?>;
<?php if ($errorResponseClassDetails) {
    echo 'use ' . $errorResponseClassDetails->getFullName() . ';';
} ?>

class <?= $class_name ?> extends <?= $errorResponseClassDetails ? 'AbstractQuery' : 'AbstractQueryWithGenericErrorResponse'?><?= "\n" ?>
{
    public function path(): string
    {
        return '<?= $endpoint ?>';
    }

    public function method(): string
    {
        return '<?= $method ?>';
    }

    public function responseClassName(): string
    {
        return <?= $responseClassNameDetails->getShortName() ?>::class;
    }

    <?php if ($errorResponseClassDetails) { ?>
    public function errorResponseClassName(): string
    {
        return <?= $errorResponseClassDetails->getShortName() ?>::class;
    }
    <?php } ?>

    public function support(ClientConfigurationInterface $clientConfiguration): bool
    {
        return $clientConfiguration instanceof <?= $clientShortName ?>;
    }
}
