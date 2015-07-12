<?php

namespace tests\codeception\unit\modules\user\behaviors;

use Yii;
use yii\codeception\DbTestCase;
use Codeception\Specify;
use tests\codeception\fixtures\UserFixture;
use tests\codeception\fixtures\GroupFixture;
use tests\codeception\fixtures\SpaceFixture;
use tests\codeception\fixtures\SpaceMembershipFixture;
use tests\codeception\fixtures\WallFixture;
use tests\codeception\fixtures\WallEntryFixture;
use tests\codeception\fixtures\ContentFixture;
use tests\codeception\fixtures\PostFixture;
use tests\codeception\fixtures\UserFollowFixture;
use humhub\modules\post\models\Post;
use humhub\modules\dashboard\components\actions\DashboardStream;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use humhub\modules\activity\models\Activity;
use humhub\modules\content\models\Content;

class FollowableTest extends DbTestCase
{

    use Specify;

    /**
     * @inheritdoc
     */
    public function fixtures()
    {
        return [
            'user' => [
                'class' => UserFixture::className(),
                'dataFile' => '@tests/codeception/unit/fixtures/data/user.php'
            ],
            'user_follow' => [
                'class' => UserFollowFixture::className(),
                'dataFile' => '@tests/codeception/unit/fixtures/data/user_follow.php'
            ],
            'space' => [
                'class' => SpaceFixture::className(),
                'dataFile' => '@tests/codeception/unit/fixtures/data/space.php'
            ],
        ];
    }

    public function testFollow()
    {
        Yii::$app->user->switchIdentity(User::findOne(['id' => 1]));

        // Already followed by fixture
        $user = $this->getUserByPk(2);
        $this->assertTrue($user->follow());

        // Follow 
        $user = $this->getUserByPk(3);
        $this->assertTrue($user->follow());
    }

    public function testUnfollow()
    {
        Yii::$app->user->switchIdentity(User::findOne(['id' => 1]));

        // Already followed by fixture
        $user = $this->getUserByPk(2);
        $this->assertTrue($user->unfollow());

        $user = $this->getUserByPk(3);
        $this->assertFalse($user->unfollow());
    }

    public function testFollowedBy()
    {

        Yii::$app->user->switchIdentity(User::findOne(['id' => 1]));

        $user = $this->getUserByPk(2);
        $this->assertTrue($user->isFollowedByUser());

        $user2 = $this->getUserByPk(3);
        $this->assertFalse($user2->isFollowedByUser());
    }

    public function testFollowerCount()
    {
        Yii::$app->user->switchIdentity(User::findOne(['id' => 1]));


        $user = $this->getUserByPk(2);
        $this->assertEquals(1, $user->getFollowerCount());

        $space = Space::findOne(['id' => 3]);
        $space->follow(1);
        $space->follow(2);
        $space->follow(3);
        $this->assertEquals(3, $space->getFollowerCount());
    }

    public function testGetFollowers()
    {
        Yii::$app->user->switchIdentity(User::findOne(['id' => 1]));

        $space = Space::findOne(['id' => 3]);
        $space->follow(1);
        $space->follow(2);
        $space->follow(3);

        $users = $space->getFollowers();
        $userIds = array_map(create_function('$user', 'return $user->id;'), $users);
        sort($userIds);
        $this->assertEquals(array(1, 2, 3), $userIds);
    }

    public function testGetFollowingCount()
    {
        Yii::$app->user->switchIdentity(User::findOne(['id' => 1]));

        $user = $this->getUserByPk(1);
        $this->assertEquals($user->getFollowingCount(User::className()), 1);
    }

    /*
      public function testGetFollowingObjects()
      {
      $user = $this->getUserByPk(1);
      $users = $user->getFollowingObjects(User::className());
      $this->assertEquals($users[0]->id, 2);
      }
     */

    public function getUserByPk($id)
    {
        return User::findOne(['id' => $id]);
    }

}
