# Github Bot
A robot for manage github repository via command of issue comment or pull request comment.   
The robot based on [swoft](https://github.com/swoft-cloud/swoft).

## Commands
All commands should place in a new line, and then the Github-Bot will follow the lines to execute the command.

### `/merge`
The bot will merge the Pull Request automatically.

### `/assign [user]`
Assign the issue or pull request to the users.   
Example:    
One user: `/assign @huangzhhui`   
Many user: `/assign @huangzhhui @huangzhhui`


### `/remove-assign [user]`
Remove the users from assignees.   
Example:    
One user: `/remove-assign @huangzhhui`   
Many user: `/remove-assign @huangzhhui @huangzhhui`

### `/need-review [user]`
Assign the users as reviewers.
Example:    
One user: `/need-review @huangzhhui`   
Many user: `/need-review @huangzhhui @huangzhhui`