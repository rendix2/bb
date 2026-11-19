<?php declare(strict_types=1);

namespace App\UI\Web\User\Edit;

/**
 * class EditUserProfileValues
 *
 * @package App\UI\Web\User\Edit
 */
final class EditUserProfileValues
{
    // bb: only in the form when users.canChangeUserName allows the change
    public ?string $username = null;

    public string $name;

    public string $surname;

    // bb: the settings of the forum
    public int $language;

    public string $signature;
}
