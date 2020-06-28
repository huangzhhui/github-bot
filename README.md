# Github Bot
A robot for manage github repository via command of issue comment or pull request comment.   
The robot based on [Hyperf framework](https://github.com/hyperf/hyperf).

## Commands
All commands should place in a new line, and then the Github-Bot will follow the lines to execute the command.

### `/merge`
The bot will merge the Pull Request automatically.

### `/assign [user]`
Assign the issue or pull request to the users.   
Parameters:   
`user`: *[REQUIRED]* the users who you want to assign.    
Example:    
One user: `/assign @huangzhhui`   
Many user: `/assign @huangzhhui @huangzhhui`

### `/remove-assign [user]`
Remove the users from assignees.   
Parameters:   
`user`: *[REQUIRED]* the users who you want to remove.    
Example:    
One user: `/remove-assign @huangzhhui`   
Many user: `/remove-assign @huangzhhui @huangzhhui`

### `/need-review [user]`
Assign the users as reviewers.
Parameters:   
`user`: *[REQUIRED]* the users who you want to assign as reviewers.    
Example:    
One user: `/need-review @huangzhhui`   
Many user: `/need-review @huangzhhui @huangzhhui`

### `/switch-to [type]`
Switch this issue to `question`, `bug` or `feature request`
Parameters:   
`type`: *[REQUIRED]* the type of the issues, `question|bug\feature`  
Example:    
Switch to support question: `/switch-to question`
Switch to bug report: `/switch-to bug`
