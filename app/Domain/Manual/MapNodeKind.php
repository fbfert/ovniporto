<?php

namespace App\Domain\Manual;

/** What a branch of a chapter's mind map stands for; the front end colours it by kind. */
enum MapNodeKind: string
{
    case Screen = 'screen';
    case Action = 'action';
    case State = 'state';
    case Care = 'care';
}
