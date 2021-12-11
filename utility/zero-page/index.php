<?php

  $quotes = [
      ['quote' => 'The secret of getting ahead is getting started.', 'author' => 'Mark Twain'],
      ['quote' => 'It’s hard to beat a person who never gives up.', 'author' => 'Babe Ruth'],
      ['quote' => 'Everything you can imagine is real.', 'author' => 'Pablo Picasso'],
      ['quote' => 'Do one thing every day that scares you.', 'author' => 'Eleanor Roosevelt'],
      ['quote' => 'Simplicity is the ultimate sophistication.', 'author' => 'Leonardo da Vinci'],
      ['quote' => 'Well begun is half done.', 'author' => 'Aristotle'],
      ['quote' => 'It always seems impossible until it is done.', 'author' => 'Nelson Mandela'],
      ['quote' => 'Happiness is not something ready made. It comes from your own actions.', 'author' => 'Dalai Lama'],
      ['quote' => 'Whatever you are, be a good one.', 'author' => 'Abraham Lincoln'],
      ['quote' => 'Impossible is just an opinion.', 'author' => 'Paulo Coelho'],
      ['quote' => 'Your passion is waiting for your courage to catch up.', 'author' => 'Isabelle Lafleche'],
      ['quote' => 'If opportunity doesn\'t knock, build a door.', 'author' => 'Kurt Cobain'],
      ['quote' => 'Work hard in silence, let your success be the noise.', 'author' => 'Frank Ocean'],
      ['quote' => 'Work hard, be kind, and amazing things will happen.', 'author' => 'Conan O’Brien'],
      ['quote' => 'In the middle of every difficulty lies opportunity.', 'author' => 'Albert Einstein']
  ];
  
  $randquote = array_rand($quotes);
  
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo substr($quotes[$randquote]['quote'], 0, 21); ?>...</title>
    <style>
        html {
            font-family: Arial, sans-serif;
            color: #000;
            font-size: 16px;
            font-weight: 400;
        }
        main {
            margin: 5rem 0 0 5rem;
        }
        h1,
        p {
            margin-top: 0;
            margin-bottom: 2rem;
        }
        h1 {
            font-weight: 700;
            font-size: 4.5rem;
        }
        p {
            color: #7d7d7d;
            font-size: 1.75rem;
        }
        @media screen and (max-width: 768px) {
            html {
                font-size: 12px;
            }

            main {
                margin: 3rem 0 0 3rem;
            }
        }
    </style>
</head>

<body>
    <main>
        <h1><?php echo $quotes[$randquote]['quote']; ?></h1>
        <p><?php echo $quotes[$randquote]['author']; ?></p>
    </main>
</body>

</html>