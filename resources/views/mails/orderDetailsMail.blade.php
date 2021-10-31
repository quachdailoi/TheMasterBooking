<b>Cảm ơn bạn đã mua hàng tại cửa hàng!</b>
<br>
<b>Dưới đây là thông tin hóa đơn quý khách hàng vừa đặt mua</b>
<br>
    <div class="row">
        <div class="col-lg-6">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Thông tin khách hàng</h3>
          </div>
          <div class="panel-body">
          <div class="table-responsive">
              <table class="table table-hover">
                  <tbody>
                      <tr>
                          <td><b>Tên khách hàng</b></td>
                          <td>{{ $user->name }}</td>
                      </tr>
                      <tr>
                          <td><b>Số điện thoại</b></td>
                          <td>{{ $user->phone ?? "Không có" }}</td>
                      </tr>
                      <tr>
                          <td><b>Email</b></td>
                          <td>{{ $user->email ?? "Không có" }}</td>
                      </tr>
                  </tbody>
              </table>
          </div>
        </div>
        </div>
        </div>
        <div class="col-lg-6">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Thông tin giao hàng</h3>
          </div>
          <div class="panel-body">
            <table class="table table-hover">
                  <tbody>
                      <tr>
                          <td style="width: 150px;"><b>Người nhận hàng</b></td>
                          <td>{{ $order->receiver_name }}</td>
                      </tr>
                      <tr>
                          <td><b>Số điện thoại</b></td>
                          <td>{{ $order->phone }}</td>
                      </tr>
                      <tr>
                          <td><b>Email</b></td>
                          <td>{{ $order->email }}</td>
                      </tr>
                      <tr>
                          <td><b>Địa chỉ</b></td>
                          <td>{{ $order->address }}</td>
                      </tr>
                      <tr>
                        <td><b>PT. Giao hàng</b></td>
                        <td>{{ $order->shipping_method }}</td>
                      </tr>
                      <tr>
                        <td><b>PT. Thanh toán</b></td>
                        <td>{{ $order->payment_method }}</td>
                      </tr>
                  </tbody>
              </table>
          </div>
        </div>
        </div>
    </div>
    <div class="row">
        <div class="panel panel-default" >
          <div class="panel-heading">
            <h2 class="panel-title" ><b>Danh sách sản phẩm</b></h2>
          </div>
          <div class="panel-body">
            <div class="col-lg-12" >
                <div class="table-responsive">
                    <table class="table table-hovered" >
                        <thead>
                            <tr>
                                <td style="border:thin solid;" width="50px"><strong>STT</strong></td>
					          	<td style="border:thin solid;" width="150px"><strong>Tên sản phẩm</strong></td>
					          	<td style="border:thin solid;" width="150px"><strong>Đơn giá</strong></td>
					          	<td style="border:thin solid;" width="50px"><strong>Số lượng</strong></td>
					          	<td style="border:thin solid;" width="150px"><strong>Thành tiền</strong></td>
                            </tr>
                        </thead>
                        <?php $count = 0; ?>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td style="border:thin blue solid;border-style:dashed;">{{ $count = $count + 1 }}</td>
                                    <td style="border:thin blue solid;border-style:dashed;">
                                        {{ $product->name }}
                                    </td>
                                    <td style="border:thin blue solid;border-style:dashed;">
                                    {{ number_format("$product->price",0,",",".") }} vnđ
                                    </td>
                                    <td style="border:thin blue solid;border-style:dashed;">{{ $product->quantity }}</td>
                                    <td style="border:thin blue solid;border-style:dashed;">{{ number_format($product->price * $product->quantity,0,",",".") }} vnđ </td>
                                </tr>
                            @endforeach
                            <tr>
				              <td  width="150px" >
				                    <b>Ghi chú :</b>
				              </td>
				              <td colspan="4">
                                    {{ $order->notes ?? "Không có ghi chú" }}
				              </td>
				            </tr>
                            <tr>
	                            <td colspan="5">
	                            	<b>Tổng tiền : {{ number_format("$order->amount",0,",",".") }} vnđ </b>
	                            </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
          </div>
        </div>
    </div>

<p style="color:red;">Lưu ý:</p>
<br>
<p>Chúng tôi sẽ liên hệ với quý khách để xác nhận trong vòng 24h nếu có bất kỳ thay đổi về đơn hàng xin quý khách vui lòng liên hệ với cửa hàng</p>
