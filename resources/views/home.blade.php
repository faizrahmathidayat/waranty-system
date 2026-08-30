@extends('layout/layout')
@section('content')


<style type="text/css">
  .btn {

    width: 33%;
    height: 100px;
    margin-bottom: 10px;
    padding: 18px;
    font-size: 20px;
  }
</style>
<div class="container">
  <p style="margin-top: 10px; font-size: 20px;">

  </p>

  <a href="/aktifitas" class="btn btn-success">Aktifitas {{ $count_aktfs }}<br></a>
  <a href="/install" class="btn btn-primary">Install {{ $count_install }}<br></a>
  <a href="/customer" class="btn btn-warning">Customer {{ $count_cst }}<br></a>
</div>
@endsection