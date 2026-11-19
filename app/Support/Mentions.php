<?php
namespace App\Support;

use App\Models\Team;

class Mentions
{
    /** Find team members mentioned as @FirstName or @first.last / @email-prefix in a body. */
    public static function users(string $body, Team $team)
    {
        preg_match_all('/@([\w.\-]+)/u', $body, $m);
        $handles = array_map('strtolower', $m[1] ?? []);
        if (!$handles) return collect();
        return $team->members->filter(function ($u) use ($handles) {
            $first = strtolower(explode(' ', $u->name)[0]);
            $local = strtolower(explode('@', $u->email)[0]);
            return array_intersect($handles, [$first, $local, strtolower(str_replace(' ', '.', $u->name))]);
        });
    }
}
