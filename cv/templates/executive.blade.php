@php
$theme = [
  'accent' => '#7c3aed',
  'font' => 'DejaVu Sans',
  'layout' => 'two',
  'order' => ['summary','experience','projects','education','additional'],
];
@endphp
@include('cv.templates._base', ['cv'=>$cv, 'theme'=>$theme])
