<?php

class WP_REST_Categories_Controller extends WP_REST_Controller
{

  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes()
  {

    $namespace = 'api/v1';
    $base = 'categories';

    register_rest_route($namespace, '/' . $base, array(
      array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => array($this, 'create_item'),
        'permission_callback' => array($this, 'create_item_permissions_check'),
        'args'                => $this->get_endpoint_args_for_item_schema(true),
      ),
    ));

    register_rest_route($namespace, '/' . $base . '/(?P<id>\d+)', array(
      array(
        'methods'             => WP_REST_Server::EDITABLE,
        'callback'            => array($this, 'update_item'),
        'permission_callback' => array($this, 'create_item_permissions_check'),
        'args'                => $this->get_endpoint_args_for_item_schema(true),
      ),
    ));
  }

  /**
   * Create one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function create_item($request)
  {

    $post_params = $request->get_params();
    $authorization = $request->get_header('authorization');
    $host =  $request->get_header('host');
    $segment = 'products/categories';
    $result = [];
    $data = $post_params;

    if (isset($data['img_src'])) {
      $data['image']['src'] =  $data['img_src'];
      unset($data['img_src']);
    }

    $data =  json_encode($data);

    $url = "https://$host/wp-json/wc/v2/$segment";
    $headers = array(
      'Content-Type' => 'application/json; charset=utf-8',
      'Authorization' => $authorization,
    );

    $response = wp_remote_post($url, array(
      'method'  => WP_REST_Server::CREATABLE,
      'headers' => $headers,
      'body'    =>  $data,
      'data_format' => 'body',
    ));

    $status = array(
      'success' => true,
      'message' => 'message',
      'code' => 'code'
    );

    if (is_wp_error($response)) {
      // errr
      $status = [
        'post_params' => $post_params,
        'debug' => $data,
        'success' => false,
        'message' => $response->get_error_message()
      ];

      $result = null;
    } else {

      // success
      $status = [
        'success' => true,
        'message' => 'category has been added succesfully'
      ];;

      $result = json_decode(wp_remote_retrieve_body($response));
    }

    $data = $this->prepare_response_for_collecction($status, $result);

    return new WP_REST_Response($data, 200);
  }

  /**
   * Updae one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function update_item($request)
  {

    $authorization = $request->get_header('authorization');
    $host =  $request->get_header('host');
    $id = $request->get_param('id');
    $data =  array_diff_key($request->get_params(), array_flip(["id"]));
    $segment =  "products/categories/$id";
    $result = [];

    if (isset($data['name'])) {
      $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));
    }

    if (isset($data['img_src'])) {
      $data['image']['src'] =  $data['img_src'];
      unset($data['img_src']);
    }

    $data =  json_encode($data);

    $url = "https://$host/wp-json/wc/v2/$segment";
    $headers = array(
      'Content-Type' => 'application/json; charset=utf-8',
      'Authorization' => $authorization,
    );

    $response = wp_remote_post($url, array(
      'method'  => 'PUT',
      'headers' => $headers,
      'body'    =>  $data,
      'data_format' => 'body',
    ));

    $status = array(
      'success' => true,
      'message' => 'message',
      'code' => 'code'
    );

    if (is_wp_error($response)) {
      // errr
      $status = [
        'post_params' => $post_params,
        'debug' => $data,
        'success' => false,
        'message' => $response->get_error_message()
      ];

      $result = null;
    } else {

      // success
      $status = [
        'success' => true,
        'message' => 'category has been updated succesfully'
      ];;

      $result = json_decode(wp_remote_retrieve_body($response));
    }

    $data = $this->prepare_response_for_collecction($status, $result);

    return new WP_REST_Response($data, 200);
  }

  /**
   * Check if a given request has access to create items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function create_item_permissions_check($request)
  {
    return true;
  }


  /**
   * Prepare the item for create or update operation
   *
   * @param WP_REST_Request $request Request object
   * @return WP_Error|object $prepared_item
   */
  protected function prepare_item_for_database($request)
  {
    return array();
  }

  /**
   * Prepare the item for the REST response
   *
   * @param mixed $item WordPress representation of the item.
   * @param WP_REST_Request $request Request object.
   * @return mixed
   */
  public function prepare_item_for_response($item, $request)
  {
    return array();
  }

  /**
   * Prepare the response for the REST response
   *
   * @param mixed $item WordPress representation of the item.
   * @param WP_REST_Request $request Request object.
   * @return mixed
   */
  public function prepare_response_for_collecction($status, $response)
  {

    $success = $status['success'];
    unset($status['success']);

    return array(
      'success' => $success,
      'data' => $response,
      'status' => $status
    );
  }
}

add_action('rest_api_init', function () {
  $latest_posts_controller = new WP_REST_Categories_Controller();
  $latest_posts_controller->register_routes();
});
