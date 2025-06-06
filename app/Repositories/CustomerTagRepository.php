<?php

namespace App\Repositories;

use App\Http\Requests\CustomerTagRequest;
use App\Interfaces\CustomerTagInterface;
use App\Models\UserTag;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class CustomerTagRepository implements CustomerTagInterface
{
    /**
     * @return Collection|UserTag[]
     */
    function getAll(): Collection
    {
        return UserTag::all();
    }

    function find($id)
    {
        return UserTag::find($id);
    }

    function save(array $data): ?UserTag
    {
        return new UserTag();
    }

    /**
     * @param CustomerTagRequest $request
     * @param UserTag $userTag
     */
    function update(CustomerTagRequest $request, UserTag $userTag): void
    {
        try {
            if ($request->filled('name')) {
                $userTag->name = $request->input('name');
            }

            if ($request->filled('description')) {
                $userTag->description = $request->input('description');
            }

            if ($request->filled('color')) {
                $userTag->color = $request->input('color');
            }

            $userTag->save();
        } catch (Exception $e) {
            \Log::error('Failed to update user tag: ' . $e->getMessage());
        }
    }

    /**
     * @param CustomerTagRequest $request
     * @throws Throwable
     */
    function store(CustomerTagRequest $request)
    {
        try {
            $userTag = new UserTag();
            $userTag->name = $request->input('name');
            $userTag->description = $request->input('description');
            $userTag->color = $request->input('color');
            $userTag->save();
        } catch (Exception $e) {
            \Log::error('Failed to save user tag: ' . $e->getMessage());
        }
    }

    function delete(UserTag $userTag): void
    {
        $userTag->delete();
    }
}
