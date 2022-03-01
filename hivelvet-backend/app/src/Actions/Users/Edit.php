<?php

declare(strict_types=1);

/*
 * Hivelvet open source platform - https://riadvice.tn/
 *
 * Copyright (c) 2022 RIADVICE SUARL and by respective authors (see below).
 *
 * This program is free software; you can redistribute it and/or modify it under the
 * terms of the GNU Lesser General Public License as published by the Free Software
 * Foundation; either version 3.0 of the License, or (at your option) any later
 * version.
 *
 * Hivelvet is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with Hivelvet; if not, see <http://www.gnu.org/licenses/>.
 */

namespace Actions\Users;

use Actions\Base as BaseAction;
use Base;
use Enum\ResponseCode;
use Models\User;
use Validation\DataChecker;

/**
 * Class Edit.
 */
class Edit extends BaseAction
{
    /**
     * @param Base  $f3
     * @param array $params
     */
    public function save($f3, $params): void
    {
        $v    = new DataChecker();
        $form = $this->getDecodedBody();
        $user = $this->loadData($params['id']);

        $v->notEmpty()->verify('email', $form['email'], ['notEmpty' => $this->i18n->err('users.email')]);
        $v->notEmpty()->verify('username', $form['username'], ['notEmpty' => $this->i18n->err('users.username')]);
        $v->notEmpty()->verify('role', $form['role'], ['notEmpty' => $this->i18n->err('users.role')]);
        $v->notEmpty()->verify('status', $form['status'], ['notEmpty' => $this->i18n->err('users.status')]);

        if (!$user->valid()) {
            $this->renderJson([], ResponseCode::HTTP_NOT_FOUND);
        } elseif ($v->allValid()) {
            $user->email      = $form['email'];
            $user->username   = $form['username'];
            $user->role       = $form['role'];
            $user->status     = $form['status'];
            $user->updated_on = date('Y-m-d H:i:s');

            if (!empty($form['password'])) {
                $user->password = $form['password'];
            }

            try {
                $user->save();
            } catch (\Exception $e) {
                $this->renderJson(['errors' => $e->getMessage()], ResponseCode::HTTP_INTERNAL_SERVER_ERROR);

                return;
            }

            $this->renderJson([], ResponseCode::HTTP_NO_CONTENT);
        } else {
            $this->renderJson(['errors' => $v->getErrors()], ResponseCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param int $id
     */
    public function loadData($id): User
    {
        $user = new User();
        $user->load(['id = ?', [$id]]);

        return $user;
    }
}
