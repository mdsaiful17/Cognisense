@php
$theme = [
  'accent' => '#059669',
  'font' => 'DejaVu Serif',
  'layout' => 'single',
  'order' => ['summary','experience','projects','education','skills','certifications','languages','awards','volunteering','additional'],
];
@endphp
@include('cv.templates._base', ['cv'=>$cv, 'theme'=>$theme])
