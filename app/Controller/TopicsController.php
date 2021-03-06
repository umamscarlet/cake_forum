<?php
App::uses('AppController', 'Controller');

class TopicsController extends AppController {

  public $components = array('Paginator');

  public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('index', 'view');
	}

  public function index($forumId = null) {
    if (!$this->Topic->Forum->exists($forumId)) {
      throw new NotFoundException(__('Invalid forum!'), 'flash/error');
    }

    $forum = $this->Topic->Forum->read(null, $forumId);
    $this->set('forum', $forum);

    $this->Paginator->settings['contain'] = array('User', 'Post' => array('User'));
    $this->Paginator->settings['conditions'] = array('Topic.forum_id' => $forumId);
    $this->set('topics', $this->Paginator->paginate());
  }

  public function add() {
    $forums = $this->Topic->Forum->find('list');

    if ($this->request->is('post')) {
      $this->request->data['Topic']['user_id'] = $this->Auth->user('id');
      if ($this->Topic->save($this->request->data)) {
        $this->Session->setFlash(__('Topic has been created.'), 'flash/success');
        $this->redirect('/');
      }
    }

    $this->set('forums', $forums);
  }

  public function view($id) {
    if (!$this->Topic->exists($id)) {
      throw new NotFoundException(__('Invalid topic'), 'flash/error');
    }

    $topic = $this->Topic->read(null, $id);
    $this->set('topic', $topic);

    $forum = $this->Topic->Forum->read(null, $topic['Topic']['forum_id']);
    $this->set('forum', $forum);

    $this->Paginator->settings['Post']['conditions'] = array('Post.topic_id' => $topic['Topic']['id']);
    $this->Paginator->settings['Post']['contain'] = array('User');
    $this->Paginator->settings['Post']['order'] = array('Post.id' => 'DESC');
    $this->set('topics', $this->Paginator->paginate('Post'));
    $this->set('posts', $this->Paginator->paginate('Post'));
  }

}
?>
