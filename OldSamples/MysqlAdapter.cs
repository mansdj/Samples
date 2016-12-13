using MySql.Data.MySqlClient;
using System;
using System.Data;
using System.Data.Common;

namespace Data.Database
{
    /**
     *  <summary>
     *      Abstract base class for using database adapter for connecting to various data sources.  
     *  </summary>
     *  
     *  <remarks>
     *      All inheriting classes will need a minimum of a connector, query executer, non-query executer, and database closer.
     *  </remarks>
     *  
     *  <example>
     *      try
     *      {
     *          //Instantiate the adapter
     *          MysqlAdapter adapter = new MysqlAdapter();
     *          
     *          //Build a statement
     *          adapter.MysqlCommand.CommandText = "SELECT * FROM accounts WHERE id=@id1";
     *          
     *          //Prepare the statement
     *          adapter.MysqlCommand.Prepare();
     *          
     *          //Add the parameters
     *          adapter.MysqlCommand.Parameters.Add("@id1", Encryption.GetHashedString("me@example.com", EncryptionModeEnum.MD5, false));
     *          
     *          //Execute the query
     *          adapter.ExecuteQuery();
     *          
     *          //Set the items to be inserted
     *          string email = "you@example.com";
     *          string password = Encryption.GetHashedString("test" + "salt", EncryptionModeEnum.SHA1, false);
     *          string id = Encryption.GetHashedString(email, EncryptionModeEnum.MD5, false);
     *          
     *          //Build the statement
     *          adapter.MysqlCommand.CommandText = "INSERT INTO accounts SET id=@id, email=@email, password=@password";
     *          
     *          //Prepare the statment
     *          adapter.MysqlCommand.Prepare();
     *          
     *          //Add the parameters
     *          adapter.MysqlCommand.Parameters.AddWithValue("@id", id);
     *          adapter.MysqlCommand.Parameters.AddWithValue("@email", email);
     *          adapter.MysqlCommand.Parameters.AddWithValue("@password", password);
     *          
     *          //Execute the statement
     *          adapter.ExecuteNonQuery();
     *          
     *          adapter.Close();
     *          
     *      }
     *      catch (Exception e)
     *      {
     *          //Do something with exception
     *      }
     *  </example>
     *  
     */
    public class MysqlAdapter : DatabaseAdapter, IDisposable
    {
        /**
         *  <summary>
         *      Stored MysqlConnection object.
         *  </summary>
         */
        protected MySqlConnection _mysqlConnection;

        /**
         *  <summary>
         *      Stored MysqlConnection object.
         *  </summary>
         */
        protected MySqlCommand _mysqlCommand;

        /**
         *  <summary>
         *      Stored DataTable for retrieving data from queries.
         *  </summary>
         */
        protected DataTable _dbResult;

        /**
         *  <summary>
         *      Class constant for defining the server where the database is located.
         *  </summary>
         */
        private const string SERVER = "localhost";

        /**
         *  <summary>
         *      Class constant for defining the database of the application.
         *  </summary>
         */
        private const string DATABASE = "testmud";

        /**
         *  <summary>
         *      Class constant for defining the connecting database user id.
         *  </summary>
         */
        private const string USER = "root";

        /**
         *  <summary>
         *      Class constant for defining the database user password.
         *  </summary>
         */
        private const string PASSWORD = "";

        public MysqlAdapter()
        {
            //Start the connection string builder
            DbConnectionStringBuilder sb = new DbConnectionStringBuilder();

            //Add the parameters for connecting to the database
            sb.Add("SERVER", SERVER);
            sb.Add("DATABASE", DATABASE);
            sb.Add("UID", USER);
            sb.Add("PASSWORD", PASSWORD);

            //Instantiate and open the connection
            this.Connect(sb);

        }

        /**
         *  <summary>
         *      Getter and Setter for the MysqlCommand object
         *  </summary>
         */
        public MySqlCommand MysqlCommand
        {
            get
            {
                return this._mysqlCommand;
            }

            set
            {
                this._mysqlCommand = value;
            }
        }

        /**
         *  <summary>
         *      Getter and Setter for the DataTable object
         *  </summary>
         */
        public DataTable DbResult
        {
            get
            {
                return this._dbResult;
            }

            set
            {
                this._dbResult = value;
            }
        }

        /**
         *  <summary>
         *      Method for connecting to the data source.  Once connected to the 
         *      data source, the connection is assigned to the dbConnection property.
         *  </summary>
         * 
         *  <param name="connectionString">
         *      String to connect to the database
         *  </param>
         */
        protected override void Connect(DbConnectionStringBuilder connectionString)
        {
            //Create a MysqlConnection object
            this._mysqlConnection = new MySqlConnection(connectionString.ToString());

            //Open the mysql connection
            this._mysqlConnection.Open();

            //Instantiate mysqlCommand object
            this._mysqlCommand = new MySqlCommand();

            //Assign the connection property to the command object property
            this._mysqlCommand.Connection = this._mysqlConnection;
        }

        /**
         *  <summary>
         *      Method for executing SQL data set queries.  The result is assigned to the
         *      dbTable property.
         *  </summary>
         *    
         *  <exception cref="">
         *      Throws a caught exception message.
         *  <exception>
         */
        public override void ExecuteQuery()
        {
            try
            {
                //Read the stream of rows from the database
                MySqlDataReader reader = _mysqlCommand.ExecuteReader();

                //Instantiate the dbTable property for future access
                this._dbResult = new DataTable();

                //Fill the data table with the rows from the reader
                this._dbResult.Load(reader);

                //Close the reader
                reader.Close();
            }
            catch(MySqlException e)
            {
                throw new Exception(e.Message);
            }
        }

        /**
         *  <summary>
         *      Method for interacting with a database that does require the
         *      user to query the database.  
         *  </summary>
         *  
         *  <param name="statement">
         *      SQL statement for querying the database
         *  </param>
         *  
         *  <returns>
         *      Mixed integer depending on whether the statement was an insert, update,
         *      delete or other type statement.
         *  </returns>
         *  
         *  <exception cref="">
         *      Throws a caught exception message.
         *  <exception> 
         */
        public override int ExecuteNonQuery()
        {
            //Number of rows affected by command
            int rows;

            try
            {
                //Excute the command return the number rows inserted/updated/affected
                rows = _mysqlCommand.ExecuteNonQuery();
            }
            catch (MySqlException e)
            {
                throw new Exception(e.Message);
            }

            return (int) rows;
        }

        /**
         *  <summary>
         *      Method for closing the database connection.
         *  </summary>
         *  
         *  <exception cref="">
         *      Throws a caught exception message.
         *  <exception>
         */
        public override void Close()
        {
            try
            {
                //Close the connection
                this._mysqlConnection.Close();
            }
            catch(MySqlException e)
            {
                throw new Exception(e.Message);
            }
        }

        public void Dispose()
        {
        }
    }
}
