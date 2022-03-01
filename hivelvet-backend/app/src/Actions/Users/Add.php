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
use Actions\RequirePrivilegeTrait;
use Enum\ResponseCode;
use Enum\UserStatus;
use Models\User;
use Validation\DataChecker;

/**
 * Class Add.
 */
class Add extends BaseAction
{
    use RequirePrivilegeTrait;

    /**
     * @param \Base $f3
     * @param array $params
     */
    public function save($f3, $params): void
    {
        $v    = new DataChecker();
        $form = $this->getDecodedBody();
        $user = new User();

        $v->notEmpty()->verify('email', $form['email'], ['notEmpty' => $this->i18n->err('users.email')]);
        $v->notEmpty()->verify('username', $form['username'], ['notEmpty' => $this->i18n->err('users.username')]);
        $v->notEmpty()->verify('password', $form['password'], ['notEmpty' => $this->i18n->err('users.password')]);
        $v->notEmpty()->verify('role', $form['role'], ['notEmpty' => $this->i18n->err('users.role')]);

        if ($v->allValid()) {
            $user->email    = $form['email'];
            $user->username = $form['username'];
            $user->password = $form['password'];
            $user->role     = $form['role'];
            $user->status   = UserStatus::ACTIVE;

            try {
                $user->save();
            } catch (\Exception $e) {
                $this->renderJson(['errors' => $e->getMessage()], ResponseCode::HTTP_INTERNAL_SERVER_ERROR);

                return;
            }

            $this->renderJson(['data' => $user->toArray()]);
        } else {
            $this->renderJson(['errors' => $v->getErrors()], ResponseCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
