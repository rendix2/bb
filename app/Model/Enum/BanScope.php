<?php declare(strict_types=1);

namespace App\Model\Enum;

/**
 * What a ban blocks (ban.scope), chosen by the admin for every ban; the whole web is the default.
 */
enum BanScope: string
{
    /** Everything: the visitor sees only the page of the ban (and is logged out). */
    case Web = 'web';

    /** Being logged in (the account is logged out; reading as a guest stays). */
    case Login = 'login';

    /** A new account (by the e-mail or the IP address). */
    case Registration = 'registration';

    /** Writing: topics, posts, polls, thanks, private messages (reading stays). */
    case Writing = 'writing';

    public function translationKey() : string
    {
        return 'admin-ban.scope.' . $this->value;
    }
}
