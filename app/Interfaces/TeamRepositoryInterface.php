<?php

namespace App\Interfaces;

interface TeamRepositoryInterface
{
    function getAllMembers($organization, $team = null);
    function findMember($team, $id);
    function updateMemberRoles($user_id, $org_id, $roles);
    function inviteMember($data);
}
