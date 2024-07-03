{{-- @php
phpinfo();
die();
@endphp --}}
<!DOCTYPE html>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<head>
    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            border: none;
            width: 100%;
        }

        td,
        th {
            border: 1px solid #eeeeee;
            text-align: left;
        }

        tr:nth-child(even) {
            background-color: #eeeeee;
        }

        th {
            font-size: 9px;

        }

        td {
            font-size: 7px;
        }

        footer {
            width: 100%;
            position: fixed;
            bottom: 0;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <table style="margin-top: -20px !important;">
        <tr style="background-color: #5fafa3;">
            <td style="text-align: left; border :none; padding:15px;">
                <div>
                    <h3 style="color: #ffffff">CHIPTRONICS SOLUTIONS</h3>
                    <h4 style="color: #ffffff">Street Address,City, State, Zip Code</h4>
                </div>

            </td>
            <td style="text-align: right; border :none;">
                <div>
                    <img width="150" src="https://placeholderlogo.com/img/placeholder-logo-5.png">
                </div>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td style="text-align: center; border :none;">
                <div>
                    <h2>Monthly Timesheet</h2>
                </div>
            </td>
        </tr>
    </table>
    @php
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'Mon', 'Tue', 'Wed'];
    @endphp

    <table style="margin-top: 5px !important;">
        <tr style="text-align: left; border :1px solid black; width:120px;">
            <td style="text-align: left;"><b>Name</b>: Fahath</td>
            <td style="text-align: left;"><b>EID</b>: 000222</td>
            <td style="text-align: left;"><b>Dept</b>: Sales</td>
            <td style="text-align: left; width:120px;"><b>Date: </b> 1 Sep 22 to 13 Sep 22</td>
            <td style="text-align: left;"><b>Total Hrs</b>: 150</td>
            <td style="text-align: left;"><b>OT</b>: 10:31</td>
            <td style="text-align: left; color: green;"><b>Present</b>: 14</td>
            <td style="text-align: left; color: red;"><b>Absent</b>: 14</td>
            <td style="text-align: left; color: rgb(209, 139, 9);"><b>Late</b>: 14</td>


        </tr>
    </table>
    <table style="margin-top: 5px !important;">
        <tr style="background-color: #A6A6A6;">
            <td><b>Dates</b></td>
            @for ($i = 1; $i <= 31; $i++)
                <td style="text-align: center;"> {{ $i }} </td>
            @endfor
        </tr>

        <tr style="background-color: none;">
            <td> <b>Days</b> </td>
            @php
                
            @endphp
            @foreach ($days as $item)
                <td style="text-align: center;"> {{ $item }} </td>
            @endforeach

        </tr>

        <tr>
            <td> <b>In</b> </td>

            @for ($i = 1; $i <= 31; $i++)
                @php
                    $rand = rand(1, 31);
                    $min = rand(0, 9);
                    $sec = rand(0, 59);
                    if ($min < 10) {
                        $min = '0' . $min;
                    }
                    if ($sec < 10) {
                        $sec = '0' . $sec;
                    }
                    
                    $my_array = [$min . ':' . $sec, '---'];
                    shuffle($my_array);
                @endphp
                <td style=" text-align: center;"> {{ $my_array[0] == '---' ? '---' : $my_array[0] }} </td>
            @endfor

        </tr>
        <tr>
            <td> <b>Out</b> </td>

            @for ($i = 1; $i <= 31; $i++)
                @php
                    $rand = rand(1, 31);
                    $min = rand(0, 9);
                    $sec = rand(0, 59);
                    if ($min < 10) {
                        $min = '0' . $min;
                    }
                    if ($sec < 10) {
                        $sec = '0' . $sec;
                    }
                    
                    $my_array = [$min . ':' . $sec, '---'];
                    shuffle($my_array);
                @endphp
                <td style=" text-align: center;"> {{ $my_array[0] == '---' ? '---' : $my_array[0] }} </td>
            @endfor

        </tr>
        <tr>
            <td> <b>Work</b> </td>

            @for ($i = 1; $i <= 31; $i++)
                @php
                    $rand = rand(1, 31);
                    $min = rand(0, 9);
                    $sec = rand(0, 59);
                    if ($min < 10) {
                        $min = '0' . $min;
                    }
                    if ($sec < 10) {
                        $sec = '0' . $sec;
                    }
                    
                    $my_array = [$min . ':' . $sec, '---'];
                    shuffle($my_array);
                @endphp
                <td style=" text-align: center;"> {{ $my_array[0] == '---' ? '---' : $my_array[0] }} </td>
            @endfor

        </tr>
        <tr>
            <td> <b>OT</b> </td>

            @for ($i = 1; $i <= 31; $i++)
                @php
                    $rand = rand(1, 31);
                    $min = rand(0, 9);
                    $sec = rand(0, 59);
                    if ($min < 10) {
                        $min = '0' . $min;
                    }
                    if ($sec < 10) {
                        $sec = '0' . $sec;
                    }
                    
                    $my_array = [$min . ':' . $sec, '---'];
                    shuffle($my_array);
                @endphp
                <td style=" text-align: center;"> {{ $my_array[0] == '---' ? '---' : $my_array[0] }} </td>
            @endfor

        </tr>

        <tr>
            <td> <b>Late Coming</b> </td>

            @for ($i = 1; $i <= 31; $i++)
                @php
                    $rand = rand(1, 31);
                    $min = rand(0, 9);
                    $sec = rand(0, 59);
                    if ($min < 10) {
                        $min = '0' . $min;
                    }
                    if ($sec < 10) {
                        $sec = '0' . $sec;
                    }
                    
                    $my_array = [$min . ':' . $sec, '---'];
                    shuffle($my_array);
                @endphp
                <td style=" text-align: center;"> {{ $my_array[0] == '---' ? '---' : $my_array[0] }} </td>
            @endfor

        </tr>

        <tr>
            <td> <b>Early Going</b> </td>

            @for ($i = 1; $i <= 31; $i++)
                @php
                    $rand = rand(1, 31);
                    $min = rand(0, 9);
                    $sec = rand(0, 59);
                    if ($min < 10) {
                        $min = '0' . $min;
                    }
                    if ($sec < 10) {
                        $sec = '0' . $sec;
                    }
                    
                    $my_array = [$min . ':' . $sec, '---'];
                    shuffle($my_array);
                @endphp
                <td style=" text-align: center;"> {{ $my_array[0] == '---' ? '---' : $my_array[0] }} </td>
            @endfor

        </tr>

        <tr>
            <td> <b>Shift Type</b> </td>

            @for ($i = 1; $i <= 31; $i++)
                <?php
                $my_array = ['MA', 'AU', 'NO'];
                shuffle($my_array);
                ?>
                <td style=" text-align: center;"><span>{{ $my_array[0] }}</span></td>
            @endfor
        </tr>

        <tr>
            <td> <b>Shift</b> </td>

            @for ($i = 1; $i <= 31; $i++)
                <?php
                $my_array = ['Mor', 'Eve', 'Eve2'];
                shuffle($my_array);
                ?>
                <td style=" text-align: center;"><span>{{ $my_array[0] }}</span></td>
            @endfor
        </tr>

        <tr>
            <td> <b>Device In</b> </td>

            @for ($i = 1; $i <= 31; $i++)
                <?php
                $my_array = ['MED', 'BED'];
                shuffle($my_array);
                ?>
                <td style=" text-align: center;"><span>{{ $my_array[0] }}</span></td>
            @endfor
        </tr>

        <tr>
            <td> <b>Device Out</b> </td>

            @for ($i = 1; $i <= 31; $i++)
                <?php
                $my_array = ['MED', 'BED'];
                shuffle($my_array);
                ?>
                <td style=" text-align: center;"><span>{{ $my_array[0] }}</span></td>
            @endfor
        </tr>

        <tr>
            <td> <b>Status</b> </td>

            @for ($i = 1; $i <= 31; $i++)
                <?php
                $my_array = ['A', 'P'];
                shuffle($my_array);
                ?>
                <td style=" text-align: center;"><span
                        style="color:{{ $my_array[0] == 'A' ? 'red' : 'green' }};">{{ $my_array[0] }}</span></td>
            @endfor
        </tr>

    </table>
    <footer>
        <table>
            <tr>
                <td style="text-align: left;"><b>Device</b>: Main Entrance = MED, Back Entrance = BED</td>
                <td style="text-align: left;"><b>Shift Type</b>: Manual = MA, Auto = AU, NO = NO</td>
                <td style="text-align: left;"><b>Shift</b>: Morning = Mor, Evening = Eve, Evening2 = Eve2</td>
                <td style="text-align: right;">
                    Date : {{ date('d/M/Y H:i:s') }}
                </td>
            </tr>
        </table>
    </footer>
</body>

</html>
