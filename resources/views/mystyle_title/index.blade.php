<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Mystyle Title</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
   <div class="container">
       <div class="row justify-content-center">
           <div class="col-md-8">
               <h4 class="p-4 text-center">Mystyle Title</h4>
            <form action="{{route('mystyle_title')}}" method="get" class="form-inline" >
                <div class="col-sm-5 form-group">
                    <div class="input-group">
                        <input class="form-control" name="date" type="date" >
                    <div class="input-group-btn ml-2">
                        <button type="submit" class="btn btn-success">Search</button>
                    </div>
                </div>
            </div>

            <table class="table mt-2">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Published Date</th>
                        <th>Crawl Date</th>
                    </tr>
                </thead>
                <tbody>
                    @if (!empty($mystyle_title))
                    @foreach ($mystyle_title as $title)
                    <tr>
                        <td>{{$title->title}}</td>
                        <td>{{$title->publishedDate}}</td>
                        <td>{{$title->created_at}}</td>

                    </tr>
                    @endforeach
                    @endif

                </tbody>
            </table>
            </form>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    {{$mystyle_title->links()}}</div>
                </div>
           </div>
       </div>
   </div>
</body>
</html>
